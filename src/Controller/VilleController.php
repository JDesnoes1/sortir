<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleType;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ville', name: 'ville_')]
class VilleController extends AbstractController
{
    #[Route('/', name: 'list')]
    public function list(VilleRepository $villeRepository): Response
    {
        $villes= $villeRepository->findAll();



        return $this->render('ville/villes.html.twig', [
            'villes' => $villes
        ]);
    }

    #[Route('/add', name: 'add')]
    public function add(
        Request $request,
        VilleRepository $villeRepository
    ):Response{

        $ville= new Ville();
        $villeForm = $this->createForm(VilleType::class,$ville);

        $villeForm->handleRequest($request);

        if ($villeForm->isSubmitted()){
            $villeRepository->save($ville,true);
            return $this->redirectToRoute('ville_list');
        }
        return $this->render('ville/add.html.twig',[
            'villeForm'=> $villeForm->createView()
        ]);

    }




    #[Route('/update/{id}', name: 'update', requirements: ["id"=>"\d+"])]
    public function edit(int $id, VilleRepository $villeRepository, Request $request){

        $ville=$villeRepository->find($id);

        $villeForm =$this->createForm(VilleType::class, $ville);

        $villeForm->handleRequest($request);

        if ($villeForm->isSubmitted()){
            $villeRepository->save($ville,true);
            return $this->redirectToRoute('ville_list');
        }

        return $this->render('ville/update.html.twig',[
            'villeForm'=>$villeForm->createView()
        ]);
    }

    #[Route('/delete/{id}', name:'delete', requirements: ["id"=>"\d+"])]
    public function delete(int $id, VilleRepository $villeRepository){

        //On va cherche la ville à suppr en BDD et on la stocke dans $ville
        $ville=$villeRepository->find($id);

        //Suppression de la ville
        $villeRepository->remove($ville, true);



        return $this->redirectToRoute('ville_list');
    }
}
