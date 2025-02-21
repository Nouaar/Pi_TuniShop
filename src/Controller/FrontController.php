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
use App\Entity\AdresseUser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; 
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Products;
use App\Repository\ProductsRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;



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
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $userId = $request->cookies->get('user_id');
        $utilisateur = null;
    
        if ($userId) {
            $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->find($userId);
        }
    
        $session = $request->getSession();
        $cart = $session->get('cart', []);
    
        $params['isAuthenticated'] = $this->securityService->isUserAuthenticated($request);
        $params['utilisateur'] = $utilisateur;
        $params['cart'] = $cart; // ✅ Ensure cart is passed to all templates
    
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
    public function cart(SessionInterface $session): Response
{
    // Get cart data from session
    $cart = $session->get('cart', []);

    // Calculate the total price using 'product' instead of 'item'
    $total = array_reduce($cart, function ($sum, $product) {
        return $sum + ($product['price'] * $product['quantity']);
    }, 0);

    return $this->renderWithAuth('Front/cart.html.twig', [
        'cart' => $cart,
        'total' => $total,
    ]);
}


    #[Route('/cart/add/{id}', name: 'cart_add', methods: ['POST'])]
    public function addToCart($id, SessionInterface $session, ProductsRepository $productsRepository): Response
    {
        $product = $productsRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        $cart = $session->get('cart', []);

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] += 1;
        } else {
            $cart[$id] = [
                'id' => $product->getId(),
                'title' => $product->getTitle(),
                'price' => $product->getPrice(),
                'image' => $product->getImage(),
                'quantity' => 1,
            ];
        }

        $session->set('cart', $cart);

        return $this->redirectToRoute('cart');
    }

    
    #[Route('/cart/count', name: 'cart_count', methods: ['GET'])]
public function cartCount(SessionInterface $session): JsonResponse
{
    $cart = $session->get('cart', []);
    $count = array_sum(array_column($cart, 'quantity'));

    return new JsonResponse(['count' => $count]);
}

    #[Route('/cart/remove/{id}', name: 'cart_remove', methods: ['POST'])]
public function removeFromCart($id, SessionInterface $session): Response
{
    $cart = $session->get('cart', []);

    if (isset($cart[$id])) {
        if ($cart[$id]['quantity'] > 1) {
            $cart[$id]['quantity'] -= 1;
        } else {
            unset($cart[$id]);
        }
    }

    $session->set('cart', $cart);

    return $this->redirectToRoute('cart');
}



    #[Route('/search', name: 'search')]
    public function search(): Response
    {
        return $this->renderWithAuth('Front/search.html.twig');
    }

    #[Route('/shop', name: 'shop')]
    public function shop(): Response
    {
        $products = $this->entityManager->getRepository(Products::class)->findAll();
        return $this->renderWithAuth('Front/shop.html.twig', [
            'products' => $products,
        ]);
    }
    #[Route('/shop_detail/{id}', name: 'shop_detail')]
    public function shopDetail(int $id, ProductsRepository $productsRepository): Response
    {
        $product = $productsRepository->find($id);
    
        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }
    
        return $this->renderWithAuth('Front/detail.html.twig', [
            'product' => $product
        ]);
    }
    #[Route('/cart/update/{id}/{action}', name: 'cart_update')]
public function updateCart(int $id, string $action, SessionInterface $session, ProductsRepository $productRepo): Response
{
    $cart = $session->get('cart', []);

    if (isset($cart[$id])) {
        if ($action === 'increase') {
            $cart[$id]['quantity'] += 1;
        } elseif ($action === 'decrease' && $cart[$id]['quantity'] > 1) {
            $cart[$id]['quantity'] -= 1;
        } else {
            unset($cart[$id]);
        }
    }

    $session->set('cart', $cart);
    return $this->redirectToRoute('cart');
}

    
#[Route('/checkout/{id}', name: 'checkout_single')]
public function checkoutSingle(int $id, EntityManagerInterface $entityManager): Response
{
    $product = $entityManager->getRepository(Products::class)->find($id);

    if (!$product) {
        throw $this->createNotFoundException('Product not found');
    }

    return $this->renderWithAuth('Front/checkout.html.twig', [
        'cart' => [  // Pass a single product as a "cart" array
            [
                'id' => $product->getId(),
                'title' => $product->getTitle(),
                'price' => $product->getPrice(),
                'quantity' => 1, // Default quantity for Buy Now
                'image' => $product->getImage(),
            ]
        ],
        'total' => $product->getPrice() + 10, // Including shipping fee
    ]);
}

#[Route('/checkout', name: 'checkout_cart')]
public function checkoutCart(Request $request, SessionInterface $session, EntityManagerInterface $entityManager): Response
{
    // Get cart data from session
    $cart = $session->get('cart', []);

    // Check if user is directly buying a single product (from "Buy Now")
    $productId = $request->query->get('productId');
    $product = null;

    if ($productId) {
        $product = $entityManager->getRepository(Products::class)->find($productId);
        if (!$product) {
            return new Response('Product not found.', 404);
        }
    }

    // Calculate total price (if coming from cart)
    $total = array_reduce($cart, function ($sum, $product) {
        return $sum + ($product['price'] * $product['quantity']);
    }, 0);

    return $this->renderWithAuth('Front/checkout.html.twig', [
        'cart' => $cart,
        'total' => $total + 10, // Adding shipping cost
        'product' => $product // Pass the single product if "Buy Now" was used
    ]);
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
public function editProfile(
    Request $request, 
    EntityManagerInterface $entityManager, 
    UtilisateurRepository $repo
): Response {
    // Get the user ID from the cookie
    $userId = $request->cookies->get('user_id');

    // Check if the user ID exists in the cookie
    if (!$userId) {
        return $this->redirectToRoute('app_utilisateur_login');
    }

    // Retrieve the user based on the ID
    $utilisateur = $repo->find($userId);

    if (!$utilisateur) {
        return $this->redirectToRoute('home');
    }

    // Process form submission
    if ($request->isMethod('POST')) {
        $prenom = $request->request->get('prenom');
        $nom = $request->request->get('nom');
        $email = $request->request->get('email');

        // Update user fields
        $utilisateur->setNom($nom);
        $utilisateur->setEmail($email);
        $utilisateur->setPrenom($prenom);

        // Retrieve the adresses data, making sure it's an array
        $adressesData = $request->request->all('adresses');  // Retrieve all adresses data

        if (is_array($adressesData)) {
            // Iterate over each address and process delete
            foreach ($adressesData as $adresseData) {
                // Check if delete flag is set and the value is "1"
                if (isset($adresseData['delete']) && $adresseData['delete'] == '1') {
                    // Find the address to delete
                    $adresseUser = $entityManager->getRepository(AdresseUser::class)->find($adresseData['id']);
                    if ($adresseUser) {
                        // Remove address from database
                        $entityManager->remove($adresseUser);
                    }
                } else {
                    // Handle address update or new address
                    $adresse = isset($adresseData['id']) && $adresseData['id'] ? 
                               $entityManager->getRepository(AdresseUser::class)->find($adresseData['id']) : 
                               new AdresseUser();

                    // Set the address details
                    $adresse->setRue($adresseData['rue']);
                    $adresse->setVille($adresseData['ville']);
                    $adresse->setCodePostal($adresseData['code_postal']);
                    $adresse->setPays($adresseData['pays']);
                    $adresse->setUtilisateur($utilisateur); // Make sure to set the user relation

                    // Persist the new or updated address
                    $entityManager->persist($adresse);
                }
            }
        } else {
            // Handle the case where adressesData is not an array
            throw new \Exception('Expected adresses data to be an array.');
        }

        // Save all changes to the database
        $entityManager->flush();

        // Add a success message
        $this->addFlash('success', 'Profile and addresses updated successfully!');

        // Redirect back to the homepage or the appropriate route
        return $this->redirectToRoute('home', ['id' => $utilisateur->getId()]);
    }

    // If the form was not submitted, render the edit profile page with the current user data
    return $this->renderWithAuth('front/index.html.twig', [
        'utilisateur' => $utilisateur,
    ]);
}



#[Route('/change-password', name: 'change_password', methods: ['POST'])]
public function changePassword(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
{
    // Get the user ID from the cookie
    $userId = $request->cookies->get('user_id');

    if (!$userId) {
        return new JsonResponse(['status' => 'error', 'message' => 'User session expired. Please log in again.'], 400);
    }

    // Retrieve the user based on the ID
    $user = $entityManager->getRepository(Utilisateur::class)->find($userId);

    if (!$user) {
        return new JsonResponse(['status' => 'error', 'message' => 'User not found.'], 400);
    }

    // Get current, new, and confirm passwords from the request
    $currentPassword = $request->request->get('current_password');
    $newPassword = $request->request->get('new_password');
    $confirmPassword = $request->request->get('confirm_password');

    // Validate the current password
    if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
        return new JsonResponse(['status' => 'error', 'message' => 'Current password is incorrect.'], 400);
    }

    // Ensure new password is different from the old one
    if ($passwordHasher->isPasswordValid($user, $newPassword)) {
        return new JsonResponse(['status' => 'error', 'message' => 'New password must be different from the old password.'], 400);
    }

    // Check if the new password and confirm password match
    if ($newPassword !== $confirmPassword) {
        return new JsonResponse(['status' => 'error', 'message' => 'The new passwords do not match.'], 400);
    }

    // Hash the new password before storing it
    $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
    $user->setPassword($hashedPassword);

    // Persist the changes in the database
    $entityManager->persist($user);
    $entityManager->flush();

    return new JsonResponse(['status' => 'success', 'message' => 'Password successfully changed!'], 200);
}


}

