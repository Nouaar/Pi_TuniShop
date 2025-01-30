<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\SecurityService;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;

final class FrontController extends AbstractController
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
    

    #[Route('/home', name: 'home')]
    public function home(): Response
    {
        return $this->renderWithAuth('front/index.html.twig');
    }
    


    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->renderWithAuth('Front/about.html.twig');
    }

    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        return $this->renderWithAuth('Front/contact.html.twig');
    }

    #[Route('/help', name: 'help')]
    public function help(): Response
    {
        return $this->renderWithAuth('Front/help.html.twig');
    }

    #[Route('/faqs', name: 'faqs')]
    public function faqs(): Response
    {
        return $this->renderWithAuth('Front/faqs.html.twig');
    }

    #[Route('/sign_in', name: 'sign_in')]
    public function sign_in(): Response
    {
        return $this->renderWithAuth('Front/SignUp_LogIn_Form.html.twig');
    }

    #[Route('/wishlist', name: 'wishlist')]
    public function wishlist(): Response
    {
        return $this->renderWithAuth('Front/wishlist.html.twig');
    }

    #[Route('/cart', name: 'cart')]
    public function cart(): Response
    {
        return $this->renderWithAuth('Front/cart.html.twig');
    }

    #[Route('/search', name: 'search')]
    public function search(): Response
    {
        return $this->renderWithAuth('Front/search.html.twig');
    }

    #[Route('/shop', name: 'shop')]
    public function shop(): Response
    {
        return $this->renderWithAuth('Front/shop.html.twig');
    }

    #[Route('/shop_detail', name: 'shop_detail')]
    public function shop_detail(): Response
    {
        return $this->renderWithAuth('Front/detail.html.twig');
    }

    #[Route('/checkout', name: 'checkout')]
    public function checkout(): Response
    {
        return $this->renderWithAuth('Front/checkout.html.twig');
    }

    #[Route('/logout', name: 'logout')]
    public function logout(Request $request): Response
    {
        // Invalidate session
        $session = $request->getSession();
        $session->invalidate();

        // Clear authentication token (if using Symfony security)
        $this->container->get('security.token_storage')->setToken(null);

        // Clear JWT token cookie if using JWT authentication
        $response = $this->redirectToRoute('home');
        $response->headers->clearCookie('jwt_token');

        return $response;
    }

    #[Route('/edit_profile', name: 'edit_profile', methods: ['GET', 'POST'])]
public function editProfile(Request $request, EntityManagerInterface $entityManager, UtilisateurRepository $repo): Response
{
    // Get the user ID from the cookie
    $userId = $request->cookies->get('user_id');

    // Check if the user ID exists in the cookie
    if (!$userId) {
        // Redirect to login or home page if no user ID is found
        return $this->redirectToRoute('app_utilisateur_login');
    }

    // Retrieve the user based on the ID
    $utilisateur = $repo->find($userId);

    // Check if the user is found
    if (!$utilisateur) {
        return $this->redirectToRoute('home');
    }

    // If the form is submitted, process the data
    if ($request->isMethod('POST')) {
        $prenom = $request->request->get('prenom');
        $nom = $request->request->get('nom');
        $email = $request->request->get('email');

        // Update user fields
        $utilisateur->setNom($nom);
        $utilisateur->setEmail($email);
        $utilisateur->setPrenom($prenom);

        // Persist the changes to the database
        $entityManager->flush();

        // Add a flash message to indicate success
        $this->addFlash('success', 'Profile updated successfully!');

        // Redirect to the home page with the updated user ID
        return $this->redirectToRoute('home', ['id' => $utilisateur->getId()]);
    }

    return $this->renderWithAuth('front/index.html.twig', [
        'utilisateur' => $utilisateur,  // Pass the user data to the template
    ]);
}


}    
