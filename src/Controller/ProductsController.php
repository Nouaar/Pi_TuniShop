<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\SecurityService;
use App\Entity\Products;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Repository\UtilisateurRepository;






final class ProductsController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SecurityService $securityService;

    public function __construct(EntityManagerInterface $entityManager, SecurityService $securityService)
    {
        $this->entityManager = $entityManager;
        $this->securityService = $securityService;
    }
    
    #[Route('/products', name: 'app_products')]
    public function index(): Response
    {
        return $this->renderWithAuth('products/index.html.twig');
    }





    #[Route('/products', name: 'product_add', methods: ['GET', 'POST'])]
    public function addProduct(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        UtilisateurRepository $utilisateurRepository // Inject Utilisateur repository
    ): Response {
        if ($request->isMethod('POST')) {
            $product = new Products();
    
            // Get user_id from cookies
            $userId = $request->cookies->get('user_id'); // Assuming the cookie name is 'user_id'
    
            if (!$userId) {
                $this->addFlash('error', 'User ID not found in cookies.');
                return $this->redirectToRoute('login'); // Redirect to login or another appropriate route
            }
    
            // Find the user by user_id
            $utilisateur = $utilisateurRepository->find($userId);
    
            if (!$utilisateur) {
                $this->addFlash('error', 'User not found.');
                return $this->redirectToRoute('login'); // Redirect to login or another appropriate route
            }
    
            $product->setUtilisateur($utilisateur); // Set the user for the product
    
            // Set product details from form data
            $product->setTitle($request->request->get('title'));
            $product->setPrice($request->request->get('price'));
            $product->setDescription($request->request->get('description'));
            $product->setCategorie($request->request->get('categorie'));
            $product->setLocation($request->request->get('location'));
            $product->setQuantity($request->request->get('quantity'));
    
            // Handle Image Upload
            $imageFile = $request->files->get('image');
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
    
                try {
                    // Move the uploaded image to the specified directory
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $product->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload image.');
                    return $this->renderWithAuth('products/index.html.twig');
                }
            }
    
            // Persist product to the database
            $entityManager->persist($product);
            $entityManager->flush();
    
            $this->addFlash('success', 'Product added successfully!');
            return $this->renderWithAuth('products/index.html.twig');
        }
    
        return $this->renderWithAuth('products/index.html.twig');
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
