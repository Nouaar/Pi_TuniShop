<?php

// src/Controller/CheckoutController.php
namespace App\Controller;

use App\Entity\Checkout;
use App\Entity\Products;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Knp\Snappy\Pdf;

use App\Service\SecurityService;
use App\Repository\CheckoutRepository;

class CheckoutController extends AbstractController
{
    private $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }
    // Renders the checkout form page
    // #[Route('/checkout', name: 'app_checkout')]
    // public function index(EntityManagerInterface $entityManager): Response
    // {
    //     // Fetch product data (assuming you want to display product info on the checkout page)
    //     $product = $entityManager->getRepository(Products::class)->find(1);  // Example product, adjust as needed
        
    //     return $this->render('checkout/index.html.twig', [
    //         'product' => $product,  // Passing the product to be used in the form
    //     ]);
    // }

    // Saves the checkout data into the database
    #[Route('/checkout/save', name: 'checkout_save', methods: ['POST'])]
    public function saveCheckout(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $email = $request->request->get('email');
        $user = $entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);
    
        if (!$user) {
            return new Response('User not found.', 404);
        }
    
        $productIds = $request->request->all('productIds');
    
        if (empty($productIds)) {
            return new Response('No products selected for checkout.', 400);
        }
    
        $products = $entityManager->getRepository(Products::class)->findBy(['id' => $productIds]);
    
        if (empty($products)) {
            return new Response('Products not found.', 404);
        }
    
        // ✅ Generate unique checkout ID for this session
        $checkoutId = time();
    
        foreach ($products as $product) {
            $checkout = new Checkout();
            $checkout->setCheckoutId($checkoutId);
            $checkout->setFirstName($request->request->get('firstName'));
            $checkout->setSecondName($request->request->get('lastName'));
            $checkout->setEmail($email);
            $checkout->setStreet($request->request->get('address'));
            $checkout->setCity($request->request->get('city'));
            $checkout->setPostalCode($request->request->get('zipcode'));
            $checkout->setCountry($request->request->get('country'));
            $checkout->setIdUser($user);
            $checkout->setIdProduit($product);
    
            $entityManager->persist($checkout);
        }
    
        $entityManager->flush();
        $session->set('cart', []);
    
        return $this->redirectToRoute('list_of_checkouts', ['userId' => $user->getId()]);
    }
    
#[Route('/listofcheckouts/{userId}', name: 'list_of_checkouts')]
public function listCheckouts(int $userId, Request $request, EntityManagerInterface $entityManager): Response
{
    $session = $request->getSession();
    $cart = $session->get('cart', []);

    // Fetch all checkouts for the user
    $checkouts = $entityManager->getRepository(Checkout::class)->findBy(['id_user' => $userId]);

    // Ensure checkouts exist
    if (!$checkouts) {
        $checkouts = [];
    }

    // Group checkouts by checkoutId
    $groupedCheckouts = [];
    foreach ($checkouts as $checkout) {
        $checkoutId = $checkout->getCheckoutId();

        if (!isset($groupedCheckouts[$checkoutId])) {
            $groupedCheckouts[$checkoutId] = [
                'checkoutId' => $checkoutId,
                'user' => $checkout,
                'products' => [],
                'totalPrice' => 0
            ];
        }

        $groupedCheckouts[$checkoutId]['products'][] = $checkout->getIdProduit();
        $groupedCheckouts[$checkoutId]['totalPrice'] += $checkout->getIdProduit()->getPrice();
    }

    return $this->renderWithAuth('checkout/listofcheckouts.html.twig', [
        'groupedCheckouts' => $groupedCheckouts, // ✅ Correct variable name
        'cart' => $cart,
    ], $entityManager);
}
    
//update for frontend checkout list page

#[Route('/checkout/update/{id}/{productId}', name: 'checkout_update_frontend')]
public function updateCheckout(int $id, int $productId, Request $request, EntityManagerInterface $entityManager): Response
{
    $checkout = $entityManager->getRepository(Checkout::class)->find($id);
    if (!$checkout) {
        throw $this->createNotFoundException('Checkout not found');
    }

    $product = $entityManager->getRepository(Products::class)->find($productId);
    if (!$product) {
        throw $this->createNotFoundException('Product not found');
    }

    if ($request->isMethod('POST')) {
        $checkout->setFirstName($request->request->get('firstName'));
        $checkout->setSecondName($request->request->get('secondName'));
        $checkout->setEmail($request->request->get('email'));
        $checkout->setStreet($request->request->get('street'));
        $checkout->setCity($request->request->get('city'));
        $checkout->setPostalCode($request->request->get('postalCode'));
        $checkout->setCountry($request->request->get('country'));
        $checkout->setIdProduit($product);

        $entityManager->flush();

        return $this->redirectToRoute('list_of_checkouts', ['userId' => $checkout->getIdUser()->getId()]);
    }

    return $this->render('checkout/checkout_update.html.twig', [
        'checkout' => $checkout,
        'product' => $product,
    ]);
}

// Delete Checkout
#[Route('/checkout/remove/{id}', name: 'checkout_remove')]
public function removeCheckout(int $id, EntityManagerInterface $entityManager): Response
{
    $checkout = $entityManager->getRepository(Checkout::class)->find($id);
    if (!$checkout) {
        throw $this->createNotFoundException('Checkout not found');
    }

    $entityManager->remove($checkout);
    $entityManager->flush();

    return $this->redirectToRoute('list_of_checkouts', ['userId' => $checkout->getIdUser()->getId()]);
}



#[Route('/checkout/pdf/{checkoutId}', name: 'checkout_pdf')]
public function generatePdf(int $checkoutId, EntityManagerInterface $entityManager, Pdf $pdf): Response
{
    // Fetch checkout details
    $checkouts = $entityManager->getRepository(Checkout::class)->findBy(['checkoutId' => $checkoutId]);
    if (!$checkouts) {
        throw $this->createNotFoundException('Checkout not found.');
    }

    $checkout = $checkouts[0]; // Get first entry
    $totalPrice = array_reduce($checkouts, fn($sum, $c) => $sum + $c->getIdProduit()->getPrice(), 0);

    // Render the PDF template
    $html = $this->renderView('checkout/pdf.html.twig', [
        'checkout' => $checkout,
        'products' => array_map(fn($c) => $c->getIdProduit(), $checkouts),
        'totalPrice' => $totalPrice
    ]);

    // Generate PDF
    $pdfContent = $pdf->getOutputFromHtml($html);

    return new Response($pdfContent, 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="checkout_' . $checkoutId . '.pdf"'
    ]);
}

private function renderWithAuth(string $template, array $params = [], EntityManagerInterface $entityManager): Response
{
    $request = $this->container->get('request_stack')->getCurrentRequest();
    $userId = $request->cookies->get('user_id');
    $utilisateur = null;

    if ($userId) {
        $utilisateur = $entityManager->getRepository(Utilisateur::class)->find($userId);
    }

    $session = $request->getSession();
    $cart = $session->get('cart', []);

    $params['isAuthenticated'] = $this->securityService->isUserAuthenticated($request);
    $params['utilisateur'] = $utilisateur;
    $params['cart'] = $cart; // ✅ Ensure cart is passed to all templates

    return $this->render($template, $params);
}
//checkout edit method that returns you to the list of checkouts page while conveying two data: the product ID and the checkouts(necessary for )
#[Route('/listofcheckouts/product/{productId}', name: 'list_of_checkouts_by_product')]
public function listCheckoutsByProduct(int $productId, EntityManagerInterface $entityManager): Response
{
    // Fetch product by its ID
    $product = $entityManager->getRepository(Products::class)->find($productId);

    if (!$product) {
        return new Response('Product not found.', 404);
    }

    // Fetch checkouts associated with the specific product
    $checkouts = $entityManager->getRepository(Checkout::class)
        ->findBy(['id_produit' => $product]);

    return $this->renderWithAuth('Back/Admincheckout/editcheckout.html.twig', [
        'checkouts' => $checkouts,
        'productTitle' => $product->getTitle(),
        'productId' => $productId,  // Passing the product title to the template
    ], $entityManager);
}


//checkout delete method that returns you to the list of checkouts page while conveying two data: the product ID and the checkout ID 
#[Route('/checkout/delete/{id}/{productId}', name: 'checkout_delete', methods: ['POST'])]
public function deleteCheckout(int $id, int $productId, EntityManagerInterface $entityManager): RedirectResponse
{
    // Fetch the checkout entry by its ID
    $checkout = $entityManager->getRepository(Checkout::class)->find($id);

    if (!$checkout) {
        // If the checkout entry is not found, return an error response or redirect
        $this->addFlash('error', 'Checkout not found!');
        return $this->redirectToRoute('list_of_checkouts_by_product', ['productId' => $productId]);
    }

    // If found, remove the checkout from the database
    $entityManager->remove($checkout);
    $entityManager->flush();

    // Add a success message and redirect back to the checkouts list page
    $this->addFlash('success', 'Checkout deleted successfully.');
    return $this->redirectToRoute('list_of_checkouts_by_product', ['productId' => $productId]);
}

// src/Controller/CheckoutController.php

#[Route('/checkout/edit/{id}/{productId}', name: 'checkout_update')]
public function editCheckout(int $id, int $productId, Request $request, EntityManagerInterface $entityManager): Response
{
    // Fetch the checkout by its ID
    $checkout = $entityManager->getRepository(Checkout::class)->find($id);

    if (!$checkout) {
        $this->addFlash('error', 'Checkout not found!');
        return $this->redirectToRoute('list_of_checkouts_by_product', ['productId' => $productId]);
    }

    // If the form is submitted
    if ($request->isMethod('POST')) {
        $checkout->setFirstName($request->get('firstName'));
        $checkout->setSecondName($request->get('lastName'));
        $checkout->setEmail($request->get('email'));
        $checkout->setStreet($request->get('street'));
        $checkout->setCity($request->get('city'));
        $checkout->setPostalCode($request->get('postalCode'));
        $checkout->setCountry($request->get('country'));

        // Persist the changes to the database
        $entityManager->flush();

        // Redirect after the update
        $this->addFlash('success', 'Checkout updated successfully!');
        return $this->redirectToRoute('list_of_checkouts_by_product', ['productId' => $productId]);
    }

    // Render the update form with the checkout details
    return $this->renderWithAuth('Back/Admincheckout/update_checkout.html.twig', [
        'checkout' => $checkout,
        'productId' => $productId
    ], $entityManager);
}


#[Route('/checkoutback', name: 'checkout_list')]
public function checkoutList(CheckoutRepository $checkoutRepository, EntityManagerInterface $entityManager): Response
{
    // Fetch all checkouts from the database
    $checkouts = $checkoutRepository->findAll();

    return $this->renderWithAuth('Back/Admincheckout/checkoutback.html.twig', [
        'checkouts' => $checkouts,
    ], $entityManager);
}

#[Route('/checkout/{id}', name: 'checkout_show')]
public function checkoutShow(int $id, CheckoutRepository $checkoutRepository, EntityManagerInterface $entityManager): Response
{
    // Fetch checkout by ID
    $checkout = $checkoutRepository->find($id);

    // If checkout does not exist, show an error
    if (!$checkout) {
        throw $this->createNotFoundException("Checkout not found!");
    }

    // Calculate total price
    $total_price = 0;
    if ($checkout->getIdProduit()) {
        $total_price = (float) $checkout->getIdProduit()->getPrice();
    }

    return $this->renderWithAuth('Back/Admincheckout/checkout_detail.html.twig', [
        'checkout' => $checkout,
        'total_price' => $total_price,
    ], $entityManager);
}

}
?>