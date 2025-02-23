<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Repository\ReclamationRepository;
use App\Service\SecurityService;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Remboursement;
use App\Form\RemboursementType;
use App\Repository\RemboursementRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use App\Entity\Utilisateur;
use App\Entity\AdresseUser;
use App\Entity\Products;
use App\Entity\Checkout;
use App\Entity\Depot;


use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; 
use Symfony\Component\HttpFoundation\JsonResponse;







final class BackController extends AbstractController
{
    private SecurityService $securityService;
    private $entityManager;

    public function __construct(SecurityService $securityService, EntityManagerInterface $entityManager)
    {
        $this->securityService = $securityService;
        $this->entityManager = $entityManager;
    }




    private function renderWithAuth(string $template, array $params = []): Response
    {
        // Get the current request
        $request = $this->container->get('request_stack')->getCurrentRequest();
        
        // Get the user ID from the cookie
        $userId = $request->cookies->get('user_id');
        
        // Default to null if no user ID is found
        $utilisateur = null;
    
        // If user ID exists, fetch the user from the repository
        if ($userId) {
            $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->find($userId);
        }
    
        // Add the authenticated user and status to the params
        $params['isAuthenticated'] = $this->securityService->isUserAuthenticated($request);
        $params['utilisateur'] = $utilisateur;
    
        return $this->render($template, $params);
    }




    

    #[Route('/back', name: 'back')]
    public function index(UtilisateurRepository $utilisateurRepository): Response
    {
        // Fetch all utilisateurs
        $utilisateurs = $utilisateurRepository->findAll();
    
        // Fetch all adresse users
        $adresse_users = $this->entityManager->getRepository(AdresseUser::class)->findAll();
    
        // Fetch all products
        $products = $this->entityManager->getRepository(Products::class)->findAll(); // Make sure 'Product' is properly capitalized
    
        // Fetch all checkouts
        $checkouts = $this->entityManager->getRepository(Checkout::class)->findAll();
    
        // Fetch all depots
        $depots = $this->entityManager->getRepository(Depot::class)->findAll();
    
        // Fetch all reclamations
        $reclamations = $this->entityManager->getRepository(Reclamation::class)->findAll();
        $remboursements = $this->entityManager->getRepository(Remboursement::class)->findAll();  // Use a different variable name
    
        // Add the data to the params array
        return $this->renderWithAuth('back/index.html.twig', [
            'utilisateurs' => $utilisateurs, 
            'adresse_users' => $adresse_users, 
            'products' => $products,
            'checkouts' => $checkouts,
            'reclamations' => $reclamations,
            'remboursements' => $remboursements,  // Correctly pass reclamations
            'depots' => $depots
        ]);
    }
    


    #[Route('/back/reclamation/{id}', name: 'app_reclamation_showback', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function showReclamationBack(int $id, EntityManagerInterface $entityManager): Response
    {
        $reclamation = $entityManager->getRepository(Reclamation::class)->find($id);
    
        if (!$reclamation) {
            throw $this->createNotFoundException('La réclamation demandée n\'existe pas.');
        }
    
        return $this->render('Back/showback.html.twig', [
            'reclamation' => $reclamation,
            'isAuthenticated' => $this->getUser() ? true : false,
        ]);
    }
    
    #[Route('/back/remboursement/{id}', name: 'app_remboursement_showback', methods: ['GET'], requirements: ['id' => '\d+'])]
public function showRemboursementBack(int $id, EntityManagerInterface $entityManager): Response
{
    $remboursement = $entityManager->getRepository(Remboursement::class)->find($id);

    if (!$remboursement) {
        throw $this->createNotFoundException('Le remboursement demandé n\'existe pas.');
    }

    return $this->render('Back/showbackrembour.html.twig', [
        'remboursement' => $remboursement,
        'isAuthenticated' => $this->getUser() ? true : false,
    ]);
}

#[Route('/back/remboursement/{id}/edit', name: 'app_remboursement_editback', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
public function editRemboursementBack(int $id, Request $request, EntityManagerInterface $entityManager, RemboursementRepository $remboursementRepository , SessionInterface $session): Response
{
    // Récupérer le remboursement par son ID
    $remboursement = $remboursementRepository->find($id);

    if (!$remboursement) {
        throw $this->createNotFoundException('Le remboursement demandé n\'existe pas.');
    }

    // Créer le formulaire pour modifier le remboursement
    $form = $this->createForm(RemboursementType::class, $remboursement);
    $form->handleRequest($request);
    $cart = $session->get('cart', []);

    // Vérifiez si le formulaire a été soumis
    $isSubmitted = $form->isSubmitted();

    // Si le formulaire est soumis et valide
    if ($form->isSubmitted() && $form->isValid()) {
        // Sauvegarder les modifications dans la base de données
        $entityManager->flush();

        // Ajouter un message flash pour notifier la réussite
        $this->addFlash('success', 'Remboursement mis à jour avec succès.');

        // Rediriger vers la page de détail du remboursement après la mise à jour
        return $this->redirectToRoute('app_remboursement_showback', ['id' => $id]);
    }

    // Retourner la vue avec le formulaire de modification et l'état de soumission
    return $this->render('Back/editrembours.html.twig', [
        'remboursement' => $remboursement,
        'form' => $form->createView(),
        'isSubmitted' => $isSubmitted,
        'cart' => $cart,
        'isAuthenticated' => $this->getUser() ? true : false // Passez l'état de soumission
    ]);
}


#[Route('/back/remboursement/new/{id}', name: 'app_remboursement_newback', methods: ['GET', 'POST'])]
public function newRemboursementBack(Request $request, int $id ,  SessionInterface $session): Response
{

    $cart = $session->get('cart', []);
    // Utiliser find pour récupérer la réclamation par ID
    $reclamation = $this->entityManager->getRepository(Reclamation::class)->find($id);
    if (!$reclamation) {
        throw $this->createNotFoundException('Réclamation non trouvée.');
    }

    // Créez un nouvel objet Remboursement
    $remboursement = new Remboursement();
    $remboursement->setIdReclamation($reclamation); // Liaison de la réclamation

    $form = $this->createForm(RemboursementType::class, $remboursement);
    $form->handleRequest($request);
    $isSubmitted = $form->isSubmitted();

    if ($form->isSubmitted() && $form->isValid()) {
        $this->entityManager->persist($remboursement);
        $this->entityManager->flush();
        $this->addFlash('success', 'Nouveau remboursement créé avec succès.');
        return $this->redirectToRoute('back');
    }

    

    return $this->render('Back/newremboursement.html.twig', [
        'form' => $form->createView(),
        'isAuthenticated' => $this->getUser() ? true : false,
        'isSubmitted' => $isSubmitted,
        'reclamation' => $reclamation,
        'cart' => $cart // Ajoutez la réclamation pour l'affichage
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
