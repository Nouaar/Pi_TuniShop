<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Service\SecurityService;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Utilisateur;
use App\Entity\AdresseUser;
use App\Entity\Products;
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

        //fetch all products
        $products = $this->entityManager->getRepository(products::class)->findAll();
        
        // Add the utilisateurs data to the params array
        return $this->renderWithAuth('back/index.html.twig', [
            'utilisateurs' => $utilisateurs, 
            'adresse_users' => $adresse_users, 
            'products' => $products,
        ]);
        
    }








    



}
