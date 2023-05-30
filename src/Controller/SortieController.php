<?php

namespace App\Controller;

use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}

