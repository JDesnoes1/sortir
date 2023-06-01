<?php

namespace App\Controller;

use App\Form\RegistrationFormType;
use App\Repository\ParticipantRepository;
use App\Utils\Uploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/participant', name: 'participant_')]
class ParticipantController extends AbstractController
{
    #[Route('/{id}', name: 'show')]
    public function show($id, ParticipantRepository $participantRepository): Response
    {
        $participant = $participantRepository->findOneBy(['id' => $id]);
        return $this->render('participant/show.html.twig', [
            'participant' => $participant
        ]);
    }


    #[Route('/update/{id}', name: 'update')]
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
}
