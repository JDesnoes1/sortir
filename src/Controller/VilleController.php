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

#[Route('/admin/ville', name: 'ville_')]
class VilleController extends AbstractController
{
    #[Route('/', name: 'list')]
    public function list(Request $request, VilleRepository $villeRepository): Response
    {
        $searchQuery = $request->query->get('q');
        if ($searchQuery) {
            $villes = $villeRepository->searchVilles($searchQuery);
        }
        else{
            $villes = $villeRepository->findAll();
        }
        $villeForm= $this->createForm(VilleType::class);

        return $this->render('ville/list.html.twig', [
            'villes' => $villes,
            'villeForm' => $villeForm->createView()
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

        if ($villeForm->isSubmitted() && $villeForm->isValid()){
            $villeRepository->save($ville,true);
            $this->addFlash('success', 'La ville a été ajouté avec succès.');
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
