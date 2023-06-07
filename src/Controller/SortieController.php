<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\LieuType;
use App\Form\SortieType;
use App\Repository\CampusRepository;
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
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/sortie', name: 'sortie_')]
class SortieController extends AbstractController
{
    #[Route('/', name: 'list')]
    public function list(Request $request, SortieRepository $sortieRepository, EtatRepository $etatRepository, CampusRepository $campusRepository): Response
    {

        $participantId = $this->getUser()->getId();
        $campusList = $campusRepository->findAll();
        $organisateur = $request->query->get('organisateur');
        $inscrit = $request->query->get('inscrit');
        $nonInscrit = $request->query->get('nonInscrit');
        $passees = $request->query->get('passees');
        $campus = $request->query->get('campus');

        $searchQuery = $request->query->get('q');
        $dateDebut = $request->query->get('dateDebut');
        $dateFin = $request->query->get('dateFin');

        $etatPassee = $etatRepository->findOneBy(['libelle' => 'Passée']);
        $etatCloturee = $etatRepository->findOneBy(['libelle' => 'Clôturée']);
        $etatEnCours = $etatRepository->findOneBy(['libelle' => 'Activité en cours']);
        $etatHistorisee = $etatRepository->findOneBy(['libelle' => 'Historisée']);
        $etatOuvert = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
        $etatAnnulee = $etatRepository->findOneBy(['libelle' => 'Annulée']);
        $etatCreee = $etatRepository->findOneBy(['libelle' => 'Créée']);

        // Effectuez la recherche en utilisant le repository
        $sorties = $sortieRepository->searchSorties($searchQuery, $campus, $dateDebut, $dateFin, $organisateur, $inscrit, $nonInscrit, $passees, $participantId);
        foreach ($sorties as $sortie) {
            $dateDebutSortie = $sortie->getDateHeureDebut();
            $dateFinInscription = $sortie->getDateLimiteInscription();
            $dateFinActivite = clone $dateDebutSortie;
            $dateFinActivite->modify('+' . $sortie->getDuree() . ' minutes');
            $maintenant = new \DateTime();

            $diffJours = $dateDebutSortie->diff($maintenant)->days;
            if ($sortie->getEtat() !== $etatAnnulee && $sortie->getEtat() !== $etatCreee) {
                if ($maintenant >= $dateDebutSortie && $maintenant <= $dateFinActivite) {
                    $sortie->setEtat($etatEnCours);
                } elseif (($maintenant >= $dateFinInscription && $diffJours < 30 && $maintenant < $dateDebutSortie)
                    || $sortie->getParticipants()->count() == $sortie->getNbInscriptionsMax() && $maintenant < $dateDebutSortie) {
                    $sortie->setEtat($etatCloturee);
                } elseif ($maintenant >= $dateDebutSortie && $diffJours < 30) {
                    $sortie->setEtat($etatPassee);
                } elseif ($diffJours > 30) {
                    $sortie->setEtat($etatHistorisee);
                } else {
                    // Aucune condition n'est respectée, donc supprimer l'état "ouvert"
                    $sortie->setEtat($etatOuvert);
                }
            }
            $sortieRepository->save($sortie, true);
        }


        return $this->render('sortie/list.html.twig', [
            'campusChoix' => $campus,
            'sorties' => $sorties,
            'campusList' => $campusList,
            'searchQuery' => $searchQuery,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'organisateur' => $organisateur,
            'inscrit' => $inscrit,
            'nonInscrit' => $nonInscrit,
            'passees' => $passees,
        ]);
    }


    #[Route('/{id}', name: 'details', requirements: ["id" => "\d+"])]
    public function show(SortieRepository $sortieRepository, int $id, ParticipantRepository $participantRepository): Response
    {
        $sortie = $sortieRepository->find($id);
        if (!$sortie) throw $this->createNotFoundException("Oups cette sortie n'existe pas !");

        $participants = $sortie->getParticipants();

        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
            'participants' => $participants
        ]);
    }


    #[Route('/add', name: 'add')]
    public function add(
        Request               $request,
        SortieRepository      $sortieRepository,
        ParticipantRepository $participantRepository,
        LieuRepository        $lieuRepository,
        VilleRepository       $villeRepository,
        EtatRepository        $etatRepository
    ): Response
    {
        $etatCreee = $etatRepository->findOneBy(['libelle' => 'Créée']);
        $username = $this->getUser()->getUserIdentifier();
        $participant = $participantRepository->findOneBy(['username' => $username]);

        $sortie = new Sortie();
        $sortie->setDateHeureDebut(new \DateTime());
        $sortie->setDateLimiteInscription(new \DateTime());
        $sortie->setParticipant($participant);
        $sortie->setEtat($etatCreee);


        $villes = $villeRepository->findAll();

        $sortieForm = $this->createForm(SortieType::class, $sortie, ['villes' => $villes]);

        $sortie->setCampus($participant->getCampus());

        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class, $lieu);
        $lieuForm->handleRequest($request);

        $villeForm = $this->createForm(SortieType::class, $sortie, ['villes' => $villes]);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid()) {
            $lieuRepository->save($lieu, true);
            $this->addFlash('success', 'Le lieu a été ajouté avec succès.');

            // Rediriger vers la création de sortie en passant le lieu nouvellement créé
            return $this->redirectToRoute('sortie_add', [
                'lieu' => $lieu->getId(),
            ]);
        }

        // Récupérer l'ID du lieu à partir de la requête
        $lieuId = $request->query->get('lieu');
        if ($lieuId) {
            $lieu = $lieuRepository->find($lieuId);
            $sortie->setLieu($lieu);
        }

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
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


    #[Route('/update/{id}', name: 'update', requirements: ["id" => "\d+"])]
    public function update(int $id, SortieRepository $sortieRepository, Request $request, ParticipantRepository $participantRepository)
    {

        $sortie = $sortieRepository->find($id);
        $username = $this->getUser()->getUserIdentifier();
        $participant = $participantRepository->findOneBy(['username' => $username]);


        if ($sortie->getParticipant()->getId() !== $participant->getId()) {
            return $this->render('sortie/show.html.twig', [
                'sortie' => $sortie
            ]);
        }

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);


        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $sortieRepository->save($sortie, true);
            return $this->redirectToRoute('sortie_list');
        }

        return $this->render('sortie/update.html.twig', [
            'sortieForm' => $sortieForm->createView(),
            'sortie' => $sortie
        ]);

    }

    #[Route('/delete/{id}', name: 'delete', requirements: ["id" => "\d+"])]
    public function delete(int $id, SortieRepository $sortieRepository, ParticipantRepository $participantRepository)
    {

        $sortie = $sortieRepository->find($id);
        $username = $this->getUser()->getUserIdentifier();
        $participant = $participantRepository->findOneBy(['username' => $username]);

        if ($sortie->getParticipant()->getId() !== $participant->getId()) {
            return $this->render('sortie/show.html.twig', [
                'sortie' => $sortie
            ]);
        }
        //Suppression de la série
        $sortieRepository->remove($sortie, true);


        // $this->addFlash('success', $sortie->getName() . ' has been removed !');

        return $this->redirectToRoute('sortie_list');
    }

    #[Route('/inscription/{sortieId}', name: 'inscription')]
    public function inscriptionSortie(Request $request, int $sortieId, SortieRepository $sortieRepository, ParticipantRepository $participantRepository, EntityManagerInterface $entityManager, SessionInterface $session)
    {
        // Récupérer la formation et le participant correspondant
        $sortie = $sortieRepository->find($sortieId);
        $username = $this->getUser()->getUserIdentifier();
        $participant = $participantRepository->findOneBy(['username' => $username]);

        if ($sortie->getParticipant()->getId() === $participant->getId()
            || $sortie->getParticipants()->count() >= $sortie->getNbInscriptionsMax()
            || $sortie->getEtat()->getLibelle() !== "Ouverte"
            || $sortie->getDateLimiteInscription() < date('now')) {
            return $this->render('sortie/show.html.twig', [
                'sortie' => $sortie
            ]);
        }

        // Insérer le participant dans la table d'association
        $sortie->addParticipant($participant);
        $entityManager->flush();

        $redirectToList = $request->query->get('redirectToList', false);
        if ($redirectToList) {
            return $this->redirectToRoute('sortie_list');
        }
        // Rediriger ou générer une réponse appropriée
        $session->getFlashBag()->add('success', 'Vous avez été inscrit avec succès à la sortie.');
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie
        ]);
    }

    #[Route('/desinscription/{sortieId}', name: 'desinscription')]
    public function desinscriptionSortie(Request $request, int $sortieId, SortieRepository $sortieRepository, ParticipantRepository $participantRepository, EntityManagerInterface $entityManager, SessionInterface $session)
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

        $redirectToList = $request->query->get('redirectToList', false);
        if ($redirectToList) {
            return $this->redirectToRoute('sortie_list');
        }

        // Rediriger ou générer une réponse appropriée
        $session->getFlashBag()->add('success', 'Vous avez été désinscrit avec succès de la sortie.');
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie
        ]);
    }

    #[Route('/cancel/{id}/{reason}', name: 'cancel', requirements: ["id" => "\d+"])]
    public function cancel(int $id, string $reason, SortieRepository $sortieRepository, EtatRepository $etatRepository, ParticipantRepository $participantRepository)
    {
        $sortie = $sortieRepository->find($id);
        $etat = $etatRepository->findOneBy(['libelle' => 'Annulée']);
        $username = $this->getUser()->getUserIdentifier();
        $participant = $participantRepository->findOneBy(['username' => $username]);

        if ($sortie->getParticipant()->getId() !== $participant->getId()) {
            return $this->render('sortie/show.html.twig', [
                'sortie' => $sortie
            ]);
        }
        if ($sortie->getDateHeureDebut() > date('now')) {
            $sortie->setEtat($etat);
            $sortie->setInfosSortie($reason);
            $sortieRepository->save($sortie, true);
            return $this->redirectToRoute('sortie_list');
        }
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie
        ]);
    }
    #[Route('/publish/{id}', name: 'publish', requirements: ["id" => "\d+"])]
    public function publish(int $id, SortieRepository $sortieRepository,EtatRepository $etatRepository, ParticipantRepository $participantRepository)
    {

        $sortie = $sortieRepository->find($id);
        $username = $this->getUser()->getUserIdentifier();
        $participant = $participantRepository->findOneBy(['username' => $username]);
        $Ouverte = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
        
        if ($sortie->getParticipant()->getId() !== $participant->getId()) {
            return $this->render('sortie/show.html.twig', [
                'sortie' => $sortie
            ]);
        }

        $sortie->setEtat($Ouverte);
        $sortieRepository->save($sortie, true);

        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie
        ]);

    }

}

