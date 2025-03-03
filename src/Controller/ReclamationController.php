<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Entity\Checkout;
use App\Entity\Products;
use App\Repository\ReclamationRepository;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;


use App\Service\SmsService;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/reclamation')]
final class ReclamationController extends AbstractController
{



    #[Route(name: 'app_reclamation_index', methods: ['GET'])]
    public function index(ReclamationRepository $reclamationRepository, SessionInterface $session)
    {
        // Get the current session cart data
        $cart = $session->get('cart', []);
        
        // Fetch all reclamations from the database
        $reclamations = $reclamationRepository->findAll();
        
        // Pass necessary data to the template
        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $reclamations,     // Pass the reclamations
            'isAuthenticated' => $this->getUser() ? true : false,  // Check if the user is authenticated
            'cart' => $cart                      // Pass the cart data
        ]);
    }
    
    #[Route('/reclamation/new', name: 'app_reclamation_new')]
    public function new(
        Request $request, 
        EntityManagerInterface $entityManager, 
        SluggerInterface $slugger, 
        SessionInterface $session, 
        UtilisateurRepository $UtilisateurRepository
    ): Response {
        $reclamation = new Reclamation();
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);
        $cart = $session->get('cart', []);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle Checkout and Product logic
            $checkout = $reclamation->getIdCommande();
            $product = $reclamation->getIdProduit();
    
            if (!$checkout || !$product) {
                $this->addFlash('error', 'Order ID or Product ID not found.');
                return $this->redirectToRoute('app_reclamation_new');
            }
    
            // Retrieve user from cookies
            $userId = $request->cookies->get('user_id');
            if (!$userId) {
                $this->addFlash('error', 'User ID not found in cookies.');
                return $this->redirectToRoute('login');
            }
    
            $utilisateur = $UtilisateurRepository->find($userId);
            if (!$utilisateur) {
                $this->addFlash('error', 'User not found.');
                return $this->redirectToRoute('login');
            }
    
            $reclamation->setIdUser($utilisateur);
    
            // Handle photo upload
            $base64Image = $request->request->get('photo');
            if ($base64Image) {
                $data = explode(',', $base64Image);
                $decodedImage = base64_decode($data[1]);
                $fileName = uniqid() . '.png';
                $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/images/' . $fileName;
                file_put_contents($filePath, $decodedImage);
                $reclamation->setPhoto($fileName);
            }
    
            $entityManager->persist($reclamation);
            $entityManager->flush();
    
            dump("bonjour");
    
            return $this->redirectToRoute('app_reclamation_index');
        }
    
        return $this->render('reclamation/new.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
            'isAuthenticated' => $this->getUser() ? true : false,
            'cart' => $cart
        ]);
    }
    
    
    

    #[Route('/{id}', name: 'app_reclamation_show', methods: ['GET'])]
    public function show(Reclamation $reclamation , SessionInterface $session): Response
    {

        $cart = $session->get('cart', []);
        return $this->render('reclamation/show.html.twig', [
            'reclamation' => $reclamation,
             'isAuthenticated' => $this->getUser() ? true : false, // Passer la variable d'authentification
            'cart' => $cart
        ]);
    }


    #[Route('/{id}/edit', name: 'app_reclamation_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager, SessionInterface $session): Response
{
    $form = $this->createForm(ReclamationType::class, $reclamation);
    $form->handleRequest($request);
    $cart = $session->get('cart', []);

    if ($form->isSubmitted() && $form->isValid()) {
        // ✅ Récupérer l'objet Checkout et Product directement depuis les propriétés de la réclamation
        $checkout = $reclamation->getIdCommande();  // L'objet Checkout complet
        $product = $reclamation->getIdProduit();   // L'objet Product complet

        if (!$checkout || !$product) {
            $this->addFlash('error', 'Order ID or Product ID not found.');
            return $this->redirectToRoute('app_reclamation_edit', ['id' => $reclamation->getId()]);
        }

// ✅ Gestion de la photo (si une nouvelle image est envoyée)
$base64Image = $request->request->get('photo');
if ($base64Image) {
    // Check if the string contains a comma to separate the base64 data
    $data = explode(',', $base64Image);

    // Ensure there are two parts (base64 prefix and actual data)
    if (count($data) === 2) {
        $decodedImage = base64_decode($data[1]);

        // Ensure the image is decoded properly
        if ($decodedImage !== false) {
            // Sauvegarde de l'image
            $fileName = uniqid() . '.png';
            $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/images/' . $fileName;
            file_put_contents($filePath, $decodedImage);

            // Mise à jour de la photo dans la réclamation
            $reclamation->setPhoto($fileName);
        } else {
            $this->addFlash('error', 'Invalid base64 image data.');
        }
    } else {
        $this->addFlash('error', 'Invalid image data format.');
    }
}

$entityManager->flush(); // Sauvegarde les changements

// Redirection après la modification
return $this->redirectToRoute('app_reclamation_index');
    }

    return $this->render('reclamation/edit.html.twig', [
        'reclamation' => $reclamation,
        'form' => $form->createView(),
        'cart' => $cart,
        'isAuthenticated' => $this->getUser() ? true : false,
    ]);
}

    

    #[Route('/{id}', name: 'app_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }

    

}