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
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use App\Repository\ProductsRepository;

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

    #[Route('/productsadd', name: 'product_add', methods: ['GET', 'POST'])]
    public function addProduct(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        UtilisateurRepository $utilisateurRepository,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        if ($request->isMethod('POST')) {

            // CSRF Validation
            $csrfToken = $request->request->get('_csrf_token');
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('product_add', $csrfToken))) {
                throw $this->createAccessDeniedException('Invalid CSRF token');
            }

            // Check form submission
            dump($request->request->all());
            dump($request->files->all());
            
            $product = new Products();
    
            // Get user_id from cookies
            $userId = $request->cookies->get('user_id'); 

            if (!$userId) {
                $this->addFlash('error', 'User ID not found in cookies.');
                return $this->redirectToRoute('login'); 
            }
    
            // Find the user by user_id
            $utilisateur = $utilisateurRepository->find($userId);
    
            if (!$utilisateur) {
                $this->addFlash('error', 'User not found.');
                return $this->redirectToRoute('login'); 
            }
    
            $product->setUtilisateur($utilisateur); 
    
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
            return $this->redirectToRoute('app_products');
        }
    
        return $this->renderWithAuth('products/index.html.twig');
    }
    #[Route('/listproducts', name: 'list_products')]
    public function listProducts(ProductsRepository $productsRepository): Response
    {
        $products = $productsRepository->findAll();

        if (!$products) {
            $this->addFlash('error', 'No products found.');
        }

        return $this->renderWithAuth('products/listproducts.html.twig', [
            'products' => $products,
        ]);
    }
    #[Route('/product/edit/{id}', name: 'product_edit')]
public function editProduct(int $id, Request $request, EntityManagerInterface $entityManager): Response
{
    // Find the product by ID
    $product = $entityManager->getRepository(Products::class)->find($id);

    if (!$product) {
        throw $this->createNotFoundException('Product not found');
    }

    // If the form is submitted and the data is valid
    if ($request->isMethod('POST')) {
        $product->setTitle($request->get('title'));
        $product->setPrice($request->get('price'));
        $product->setDescription($request->get('description'));
        $product->setCategorie($request->get('categorie'));
        $product->setLocation($request->get('location'));
        $product->setQuantity($request->get('quantity'));

        // If an image was uploaded, handle the file upload
        $imageFile = $request->files->get('image');
        if ($imageFile) {
            // Handle image upload logic here if needed
            $newFilename = uniqid() . '.' . $imageFile->guessExtension();
            $imageFile->move($this->getParameter('images_directory'), $newFilename);
            $product->setImage($newFilename);
        }

        // Persist the changes to the database
        $entityManager->flush();

        // Add flash message and redirect
        $this->addFlash('success', 'Product updated successfully!');
        return $this->redirectToRoute('list_products');
    }

    return $this->renderWithAuth('products/edit_product.html.twig', [
        'product' => $product
    ]);
}
#[Route('/product/delete/{id}', name: 'product_delete', methods: ['POST'])]
public function deleteProduct(int $id, EntityManagerInterface $entityManager): Response
{
    // Find the product by ID
    $product = $entityManager->getRepository(Products::class)->find($id);

    if (!$product) {
        throw $this->createNotFoundException('Product not found');
    }

    // Remove the product from the database
    $entityManager->remove($product);
    $entityManager->flush();

    // Add flash message
    $this->addFlash('success', 'Product deleted successfully!');

    // Redirect back to the list of products
    return $this->redirectToRoute('list_products');
}



    private function renderWithAuth(string $template, array $params = []): Response
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $userId = $request->cookies->get('user_id');
        $utilisateur = null;

        if ($userId) {
            $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->find($userId);
        }

        $params['isAuthenticated'] = $this->securityService->isUserAuthenticated($request);
        $params['utilisateur'] = $utilisateur;

        return $this->render($template, $params);
    }
}
