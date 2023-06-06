<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\RegistrationFormType;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Utils\Uploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'participant_')]
class ParticipantController extends AbstractController
{
    #[Route('participant/{id}', name: 'show')]
    public function show($id, ParticipantRepository $participantRepository): Response
    {
        $participant = $participantRepository->findOneBy(['id' => $id]);
        return $this->render('participant/show.html.twig', [
            'participant' => $participant
        ]);
    }
    #[Route('admin/participants', name: 'list')]
    public function showAll(ParticipantRepository $participantRepository, Request $request): Response {

        $participants = $participantRepository->findAll();

        return $this->render('participant/list.html.twig', [
            'participants' => $participants,
        ]);
    }


    #[Route('participant/update/{id}', name: 'update')]
    public function update($id, Request $request,
                           ParticipantRepository $participantRepository,
                           Uploader $uploader,
                           UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $participant = $participantRepository->find($id);

        // Créer le formulaire de modification
        $form = $this->createForm(RegistrationFormType::class, $participant);

        // Gérer la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            /**
             * @var UploadedFile $file
             */
            $file = $form->get('photo')->getData();

            if ($file) {
                $newFileName = $uploader->save($file, $participant->getUserIdentifier(), $this->getParameter('upload_photo'));
                $participant->setPhoto($newFileName);
            }
            // encode the plain password
            $participant->setPassword(
                $userPasswordHasher->hashPassword(
                    $participant,
                    $form->get('Password')->getData()
                )
            );

            $participantRepository->save($participant, true);

                return $this->redirectToRoute('participant_show', ['id' => $participant->getId(),
                    'participant' => $participant
                ]);
        }

        return $this->render('participant/update.html.twig', [
            'participantForm' => $form->createView()
        ]);
    }

    #[Route('participant/toggle-active/{id}', name: 'toggle_active')]
    public function toggleActive($id, ParticipantRepository $participantRepository): Response
    {
        $participant = $participantRepository->find($id);

        if ($participant) {
            $participant->setActif(!$participant->isActif());
            $participantRepository->save($participant, true);
        }

        return $this->redirectToRoute('participant_list');
    }

    #[Route('participant/delete/{id}', name: 'delete')]
    public function delete($id, ParticipantRepository $participantRepository, EntityManagerInterface $entityManager): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $participant = $participantRepository->find($id);

        if ($participant) {
            $entityManager->remove($participant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('participant_list');
    }

    #[Route('participant/sorties/{id}', name: 'sorties')]
    public function sorties($id, SortieRepository $sortieRepository):Response
    {
        $sorties= $sortieRepository->findBy(['participant'=>$id]);
        dump($sorties);

        return $this->render('participant/sorties.html.twig', [
            'sorties' => $sorties
        ]);
    }

}
