<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Form\LieuType;
use App\Form\SortieType;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/lieu', name: 'lieu_')]
class LieuController extends AbstractController
{
    #[Route('/add', name: 'add')]
    public function add(
        Request $request,
        SortieRepository $sortieRepository,
        ParticipantRepository $participantRepository,
        LieuRepository $lieuRepository
    ): Response {
        $username = $this->getUser()->getUserIdentifier();
        $participant = $participantRepository->findOneBy(['username' => $username]);

        $sortie = new Sortie();
        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class, $lieu);
        $lieuForm->handleRequest($request);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid()) {
            $lieuRepository->save($lieu, true);
            $this->addFlash('success', 'Le lieu a été ajouté avec succès.');

            // Rediriger vers la création de sortie en passant le lieu nouvellement créé
            return $this->redirectToRoute('sortie_add', ['lieu' => $lieu->getId()]);
        }

        // Récupérer l'ID du lieu à partir de la requête
        $lieuId = $request->query->get('lieu');
        if ($lieuId) {
            $lieu = $lieuRepository->find($lieuId);
            $sortie->setLieu($lieu);
        }

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $sortie->setParticipant($participant);
            $sortieRepository->save($sortie, true);
            $this->addFlash('success', 'La sortie a été ajoutée avec succès.');
            // Vous pouvez rediriger vers la liste des sorties ou une autre page si nécessaire
            return $this->redirectToRoute('sortie_list');
        }

        return $this->render('sortie/add.html.twig', [
            'sortieForm' => $sortieForm->createView(),
            'lieuForm' => $lieuForm->createView(),
        ]);
    }

}
