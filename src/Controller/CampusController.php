<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusType;
use App\Repository\CampusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/campus', name: 'campus_')]
class CampusController extends AbstractController
{
    #[Route('/', name: 'list')]
    public function list(CampusRepository $campusRepository): Response
    {
        $campus= $campusRepository->findAll();



        return $this->render('campus/list.html.twig', [
            'campus' => $campus
        ]);
    }

    #[Route('/add', name: 'add')]
    public function add(
        Request $request,
        CampusRepository $campusRepository
    ):Response{

        $campus= new Campus();
        $campusForm = $this->createForm(CampusType::class,$campus);

        $campusForm->handleRequest($request);

        if ($campusForm->isSubmitted()){
            $campusRepository->save($campus,true);
            return $this->redirectToRoute('campus_list');
        }
        return $this->render('campus/add.html.twig',[
            'campusForm'=> $campusForm->createView()
        ]);

    }




    #[Route('/update/{id}', name: 'update', requirements: ["id"=>"\d+"])]
    public function edit(int $id, CampusRepository $campusRepository, Request $request){

        $campus=$campusRepository->find($id);

        $campusForm =$this->createForm(CampusType::class, $campus);

        $campusForm->handleRequest($request);

        if ($campusForm->isSubmitted()){
            $campusRepository->save($campus,true);
            return $this->redirectToRoute('campus_list');
        }

        return $this->render('campus/update.html.twig',[
            'campusForm'=>$campusForm->createView()
        ]);
    }

    #[Route('/delete/{id}', name:'delete', requirements: ["id"=>"\d+"])]
    public function delete(int $id, CampusRepository $campusRepository){

        //On va cherche la ville à suppr en BDD et on la stocke dans $ville
        $campus=$campusRepository->find($id);

        //Suppression de la ville
        $campusRepository->remove($campus, true);



        return $this->redirectToRoute('campus_list');
    }
}
