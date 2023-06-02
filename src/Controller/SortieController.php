<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\LieuType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
#[Route('/sortie', name: 'sortie_')]
class SortieController extends AbstractController
{
    #[Route('/', name: 'list')]
    public function list(SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository->findAll();

        return $this->render('sortie/list.html.twig', [
            'sorties' => $sorties,
        ]);
    }


    #[Route('/{id}', name : 'details', requirements: ["id"=>"\d+"])]
    public function show(SortieRepository $sortieRepository, int $id, ParticipantRepository $participantRepository): Response
    {
        $sortie= $sortieRepository->find($id);
        $participants = $sortie->getParticipants();

        if(!$sortie) throw $this->createNotFoundException("Oups cette sortie n'existe pas !");

        return $this->render('sortie/show.html.twig', [
            'sortie'=>$sortie,
            'participants' => $participants
        ]);
    }

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

        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class, $lieu);

        //A faire plus tard, tips : QueryBuilder
        /*$campus = $villeRepository->findAll();*/

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortie->setParticipant($participant);

        //Permet d'extraire les données du formulaire
            $sortieForm->handleRequest($request);

            if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
                $sortieRepository->save($sortie, true);
                $this->addFlash('success', 'La sortie a été ajoutée avec succès.');
                return $this->redirectToRoute('sortie_list');
            }

        return $this->render('sortie/add.html.twig', [
            'sortieForm' => $sortieForm->createView(),
            'lieuForm' => $lieuForm->createView()
        ]);

    }
    #[Route('/update/{id}', name: 'update', requirements: ["id" => "\d+"])]
    public function edit(int $id, SortieRepository $sortieRepository, Request $request, ParticipantRepository $participantRepository)
    {

        $sortie = $sortieRepository->find($id);
        $username = $this->getUser()->getUserIdentifier();
        $participant = $participantRepository->findOneBy(['username' => $username]);

        if($sortie->getParticipant()->getId() !== $participant->getId()){
            return $this->render('sortie/show.html.twig', [
                'sortie'=>$sortie
            ]);
        }

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()){
            $sortieRepository->save($sortie, true);
            return $this->redirectToRoute('sortie_list');
        }

        return $this->render('sortie/update.html.twig', [
            'sortieForm' => $sortieForm->createView()
        ]);

    }

    #[Route('/delete/{id}', name: 'delete', requirements: ["id" => "\d+"])]
    public function delete(int $id, SortieRepository $sortieRepository, ParticipantRepository $participantRepository)
    {

        $sortie = $sortieRepository->find($id);
        $username = $this->getUser()->getUserIdentifier();
        $participant = $participantRepository->findOneBy(['username' => $username]);

        if($sortie->getParticipant()->getId() !== $participant->getId()){
            return $this->render('sortie/show.html.twig', [
                'sortie'=>$sortie
            ]);
        }
        //Suppression de la série
        $sortieRepository->remove($sortie, true);


        // $this->addFlash('success', $sortie->getName() . ' has been removed !');

        return $this->redirectToRoute('sortie_list');
    }

    #[Route('/inscription/{sortieId}', name: 'inscription')]
    public function inscriptionSortie(int $sortieId, SortieRepository $sortieRepository, ParticipantRepository $participantRepository, EntityManagerInterface $entityManager, SessionInterface $session)
    {
        // Récupérer la formation et le participant correspondant
        $sortie = $sortieRepository->find($sortieId);
        $username = $this->getUser()->getUserIdentifier();
        $participant = $participantRepository->findOneBy(['username' => $username]);

        if($sortie->getParticipant()->getId() === $participant->getId()
            || $sortie->getParticipants()->count()>=$sortie->getNbInscriptionsMax()
            || $sortie->getEtat()->getLibelle() !=="Ouverte"
            || $sortie->getDateLimiteInscription()<date('now')){
            return $this->render('sortie/show.html.twig', [
                'sortie'=>$sortie
            ]);
        }

        // Insérer le participant dans la table d'association
        $sortie->addParticipant($participant);
        $entityManager->flush();
        // Rediriger ou générer une réponse appropriée
        $session->getFlashBag()->add('success', 'Vous avez été inscrit avec succès à la sortie.');
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie
        ]);
    }

    #[Route('/desinscription/{sortieId}', name: 'desinscription')]
    public function desinscriptionSortie(int $sortieId, SortieRepository $sortieRepository, ParticipantRepository $participantRepository, EntityManagerInterface $entityManager, SessionInterface $session)
    {
        // Récupérer la sortie et le participant correspondant
        $sortie = $sortieRepository->find($sortieId);
        $username = $this->getUser()->getUserIdentifier();
        $participant = $participantRepository->findOneBy(['username' => $username]);

        // Vérifier si le participant est inscrit à la sortie
        /*if (!$sortie->getParticipants()->contains($participant)) {
        }*/

        if ($sortie->getDateHeureDebut() < new \DateTime()) {
            return $this->render('sortie/show.html.twig', [
                'sortie' => $sortie
            ]);
        }
        // Supprimer le participant de la table d'association
        $sortie->removeParticipant($participant);
        $entityManager->flush();

        // Rediriger ou générer une réponse appropriée
        $session->getFlashBag()->add('success', 'Vous avez été désinscrit avec succès de la sortie.');
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie
        ]);
    }

    #[Route('/cancel/{id}/{reason}', name: 'cancel', requirements: ["id" => "\d+"])]
    public function cancel(int $id, string $reason,SortieRepository $sortieRepository,EtatRepository $etatRepository, ParticipantRepository $participantRepository)
    {
        $sortie = $sortieRepository->find($id);
        $etat=$etatRepository->findOneBy(['libelle'=>'Annulée']);
        $username = $this->getUser()->getUserIdentifier();
        $participant = $participantRepository->findOneBy(['username' => $username]);

        if ($sortie->getParticipant()->getId() !== $participant->getId()) {
            return $this->render('sortie/show.html.twig', [
                'sortie' => $sortie
            ]);
        }
            $sortie->setEtat($etat);
            $sortie->setInfosSortie($reason);
            $sortieRepository->save($sortie, true);
            return $this->redirectToRoute('sortie_list');

    }



}

