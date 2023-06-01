<?php

namespace App\Controller;

use App\Form\RegistrationFormType;
use App\Repository\ParticipantRepository;
use App\Form\EditUserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function update($id, Request $request, ParticipantRepository $participantRepository): Response
    {
        $participant = $participantRepository->find($id);

        // Créer le formulaire de modification
        $form = $this->createForm(RegistrationFormType::class, $participant);

        // Gérer la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $participantRepository->save($participant);
            return $this->redirectToRoute('sortie_list');
        }

        return $this->render('participant/update.html.twig', [
            'participantForm' => $form->createView(),
        ]);
    }
}
