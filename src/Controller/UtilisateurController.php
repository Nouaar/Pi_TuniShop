<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use App\Service\SecurityService; // Ensure to import your SecurityService for authentication check
use App\Entity\AdresseUser;

#[Route('/utilisateur')]
final class UtilisateurController extends AbstractController
{
    private SecurityService $securityService;
    private EntityManagerInterface $entityManager;  // Declare the property

    // Constructor should store the entity manager as a property
    public function __construct(SecurityService $securityService, EntityManagerInterface $entityManager)
    {
        $this->securityService = $securityService;
        $this->entityManager = $entityManager;  // Initialize the property
    }

    // Index page - List all users
    #[Route(name: 'app_utilisateur_index', methods: ['GET'])]
    public function index(UtilisateurRepository $utilisateurRepository): Response
    {
        return $this->renderWithAuth('utilisateur/index.html.twig', [
            'utilisateurs' => $utilisateurRepository->findAll(),
        ]);
    }

    // Create a new utilisateur
    #[Route('/new', name: 'app_utilisateur_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $utilisateur = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($utilisateur);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderWithAuth('utilisateur/new.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form->createView(),
        ]);
    }



    #[Route('/utilisateur/{id}/edit', name: 'app_utilisateur_edit', methods: ['GET', 'POST'])]
    public function editUtilisateur(int $id, Request $request, EntityManagerInterface $em, Security $security): Response
    {
        // Fetch the user by ID
        $utilisateur = $em->getRepository(Utilisateur::class)->find($id);
    
        if (!$utilisateur) {
            throw $this->createNotFoundException('User not found.');
        }
    
        // Check if the user is authenticated
        $isAuthenticated = $security->isGranted('IS_AUTHENTICATED_FULLY');
    
        // Handle POST request to update user details
        if ($request->isMethod('POST')) {
            $nom = $request->request->get('nom', $utilisateur->getNom());
            $prenom = $request->request->get('prenom', $utilisateur->getPrenom());
            $email = $request->request->get('email', $utilisateur->getEmail());
            $role = $request->request->get('role', $utilisateur->getRole()); // Get role as a string
    
            // Update user details
            $utilisateur->setNom($nom);
            $utilisateur->setPrenom($prenom);
            $utilisateur->setEmail($email);
            $utilisateur->setRole($role); // Assign role directly (not an array)
    
            $em->flush();
    
            return $this->redirectToRoute('back');
        }
    
        // Pass `isAuthenticated` to the Twig template
        return $this->render('utilisateur/edit.html.twig', [
            'utilisateur' => $utilisateur,
            'isAuthenticated' => $isAuthenticated,  // pass the variable here
        ]);
    }


    // #[Route('/{id}/edit', name: 'app_utilisateur_edit', methods: ['GET', 'POST'])]
    // public function edit(Request $request, Utilisateur $utilisateur): Response
    // {
    //     $form = $this->createForm(UtilisateurType::class, $utilisateur);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $this->entityManager->flush();

    //         return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->renderWithAuth('utilisateur/edit.html.twig', [
    //         'utilisateur' => $utilisateur,
    //         'form' => $form->createView(),
    //     ]);
    // }

 


    #[Route('/utilisateur/{id}/delete', name: 'app_utilisateur_delete', methods: ['POST'])]
    public function deleteUtilisateur(int $id, EntityManagerInterface $em): Response
    {
        $utilisateur = $em->getRepository(Utilisateur::class)->find($id);
    
        if ($utilisateur) {
            $em->remove($utilisateur);
            $em->flush();
        }
    
        return $this->redirectToRoute('back');

    }





// For Address Deletion
#[Route('/adresse_user/{id}/delete', name: 'app_adresse_user_delete', methods: ['POST'])]
public function deleteAdresseUser(int $id, EntityManagerInterface $em): Response
{
    $adresseUser = $em->getRepository(AdresseUser::class)->find($id);

    if ($adresseUser) {
        $em->remove($adresseUser);
        $em->flush();
    }

    return $this->redirectToRoute('back');
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












    
}
