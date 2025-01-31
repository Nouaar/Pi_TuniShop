<?php

namespace App\Controller;

use App\Entity\AdresseUser;
use App\Form\AdresseUserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;







#[Route('/adresse_user')]
final class AdresseUserController extends AbstractController
{
    #[Route(name: 'app_adresse_user_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $adresseUsers = $entityManager
            ->getRepository(AdresseUser::class)
            ->findAll();

        return $this->render('adresse_user/index.html.twig', [
            'adresse_users' => $adresseUsers,
         
        ]);
    }

    #[Route('/new', name: 'app_adresse_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $adresseUser = new AdresseUser();
        $form = $this->createForm(AdresseUserType::class, $adresseUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($adresseUser);
            $entityManager->flush();

            return $this->redirectToRoute('app_adresse_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('adresse_user/new.html.twig', [
            'adresse_user' => $adresseUser,
            'form' => $form,
        ]);
    }

  

    // #[Route('/{id}/edit', name: 'app_adresse_user_edit', methods: ['GET', 'POST'])]
    // public function edit(Request $request, AdresseUser $adresseUser, EntityManagerInterface $entityManager): Response
    // {
    //     $form = $this->createForm(AdresseUserType::class, $adresseUser);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $entityManager->flush();

    //         return $this->redirectToRoute('app_adresse_user_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->render('adresse_user/edit.html.twig', [
    //         'adresse_user' => $adresseUser,
    //         'form' => $form,
    //     ]);
    // }

    #[Route('/{id}', name: 'app_adresse_user_delete', methods: ['POST'])]
    public function delete(Request $request, AdresseUser $adresseUser, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $adresseUser->getId(), $request->request->get('_token'))) {
            $entityManager->remove($adresseUser);
            $entityManager->flush();
            
            // Return a JSON response indicating success
            return $this->json(['success' => true]);
        }
    
        // Return an error response if CSRF is invalid or something goes wrong
        return $this->json(['success' => false], 400);
    }



    #[Route('/{id}/edit', name: 'app_adresse_user_edit', methods: ['GET', 'POST'])]
    public function editAdresse(int $id, Request $request, EntityManagerInterface $em): Response
    {
        // Fetch the address by ID
        $adresse = $em->getRepository(AdresseUser::class)->find($id);
    
        // If address not found, throw an exception or redirect
        if (!$adresse) {
            throw $this->createNotFoundException('Address not found.');
        }
    
        // Handle POST request
        if ($request->isMethod('POST')) {
            $rue = $request->request->get('rue', '');
            $ville = $request->request->get('ville', '');
            $codePostal = $request->request->get('codePostal', '');
            $pays = $request->request->get('pays', '');
    
            // Update the address
            $adresse->setRue($rue);
            $adresse->setVille($ville);
            $adresse->setCodePostal($codePostal);
            $adresse->setPays($pays);
    
            $em->flush();
    
            return $this->redirectToRoute('back');
        }
    
        // Render the edit form (GET request)
        return $this->render('adresse/edit.html.twig', [
            'adresse' => $adresse,
        ]);
    }
    
    




    
}
