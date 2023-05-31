<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function show(SortieRepository $sortieRepository, int $id): Response
    {
        $sortie= $sortieRepository->find($id);

        if(!$sortie) throw $this->createNotFoundException("Oups cette sortie n'existe pas !");

        return $this->render('sortie/show.html.twig', [
            'sortie'=>$sortie
        ]);
    }

    #[Route('/add', name: 'add')]
    public function add(
        Request $request,
        SortieRepository $sortieRepository,
        ParticipantRepository $participantRepository,
        VilleRepository $villeRepository
    ): Response {

        $username = $this->getUser()->getUserIdentifier();
        $participant = $participantRepository->findOneBy(['username' => $username]);
        $sortie = new Sortie();

        //A faire plus tard, tips : QueryBuilder
        /*$villes = $villeRepository->findAll();*/

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortie->setParticipant($participant);



        //Permet d'extraire les données du formulaire
        $sortieForm->handleRequest($request);

        if($sortieForm->isSubmitted()){
            $sortieRepository->save($sortie, true);
            return $this->redirectToRoute('sortie_list');

        }

        return $this->render('sortie/add.html.twig', [
            'sortieForm' => $sortieForm->createView()
        ]);
    }
}

