<?php

namespace App\Controller;

use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantController extends AbstractController
{
    #[Route('/afficher/user/{id}', name: 'app_afficher_user')]
    public function showUser($id, ParticipantRepository $participantRepository): Response
    {
        $user = $participantRepository->findUserById($id);

        return $this->render('participant/index.html.twig', [
            'user' => $user
        ]);
    }


}
