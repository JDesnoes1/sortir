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

class ParticipantController extends AbstractController
{
    #[Route('/afficher/user/{id}', name: 'app_afficher_user')]
    public function showUser($id, ParticipantRepository $participantRepository): Response
    {
        $user = $participantRepository->findUserById($id);

        // Créer le formulaire de modification
        $form = $this->createForm(EditUserType::class, $user);

        return $this->render('participant/index.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/modifier/user/{id}', name: 'app_modifier_user')]
    public function editUser($id, Request $request, EntityManagerInterface $entityManager, ParticipantRepository $participantRepository): Response
    {
        $user = $participantRepository->findUserById($id);

        // Créer le formulaire de modification
        $form = $this->createForm(EditUserType::class, $user);

        // Gérer la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrer les changements dans la base de données
            $entityManager->flush();

            // Rediriger vers la page d'affichage de l'utilisateur
            return $this->redirectToRoute('sortie_list', ['id' => $user->getId()]);
        }

        return $this->render('sortie/list.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
