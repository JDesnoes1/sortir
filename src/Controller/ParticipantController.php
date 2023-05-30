<?php

namespace App\Controller;

use App\Repository\ParticipantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantController extends AbstractController
{
    #[Route('/participant', name: 'app_participant')]
    public function index(): Response
    {
        return $this->render('participant/index.html.twig', [
            'controller_name' => 'ParticipantController',
        ]);
    }

    #[Route('/{id}', name : 'details', requirements: ["id"=>"\d+"])]
    public function show(ParticipantRepository $participantRepository, int $id): Response
    {
        $participant= $participantRepository->find($id);

        if(!$participant) throw $this->createNotFoundException("Oups cette sortie n'existe pas !");

        return $this->render('sortie/show.html.twig', [
            'participant'=>$participant
        ]);


    }
}
