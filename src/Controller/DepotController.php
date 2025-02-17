<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\SecurityService;
use App\Repository\DepotRepository;
use App\Form\DepotType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Depot;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


final class DepotController extends AbstractController
{
    #[Route('/depot', name: 'app_depot')]
    public function index(): Response
    {
        return $this->render('Back/AdminDepot/depot_dashboard.html.twig', [
            'controller_name' => 'DepotController',
        ]);
    }

    #[Route('/depot/{id}/delete', name: 'app_depot_delete', methods: ['POST'])]
    public function deleteDepot(int $id, EntityManagerInterface $em): Response
    {
        $depot = $em->getRepository(Depot::class)->find($id);
    
        if ($depot) {
            $em->remove($depot);
            $em->flush();
        }
        return $this->redirectToRoute('back');
    }
    #[Route('/back/add_depot', name: 'app_add_depot')]
    public function add_depot(ManagerRegistry $manager,Request $req): Response
    {
        $em=$manager->getManager();
        $depot=new Depot();
        $form=$this->createForm(DepotType::class,$depot);
        $form->handleRequest($req);
        if($form->isSubmitted() && $form->isValid()){
        $em->persist($depot);
        $em->flush();
        return $this->redirectToRoute('back');
        }
        return $this->render('depot/AddDepot.html.twig', [
            'form' => $form->createView(),
        ]);
        
    }
    #[Route('/back/update_depot/{id}', name: 'app_depot_update')]
    public function update_depot($id,ManagerRegistry $manager,Request $req,DepotRepository $rep): Response
    {
        $em = $manager->getManager();
        $depot = $rep->find($id);
        $form = $this->createForm(DepotType::class, $depot);
        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($depot);
            $em->flush();
            return $this->redirectToRoute('back');
        
        }
        return $this->render('depot/update_depot.html.twig', [
            'form' => $form->createView(),
        ]);
    }

   

}
