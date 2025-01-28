<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FrontController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function home(): Response
    {
        return $this->render('front/index.html.twig', [
            'controller_name' => 'FrontController',
        ]);
    }

    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('Front/about.html.twig');
    }

    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        return $this->render('Front/contact.html.twig');
    }

    #[Route('/help', name: 'help')]
    public function help(): Response
    {
        return $this->render('Front/help.html.twig');
    }

    #[Route('/faqs', name: 'faqs')]
    public function faqs(): Response
    {
        return $this->render('Front/faqs.html.twig');
    }

    #[Route('/sign_in', name: 'sign_in')]
    public function sign_in(): Response
    {
        return $this->render('Front/SignUp_LogIn_Form.html.twig');
    }


    #[Route('/wishlist', name: 'wishlist')]
    public function wishlist(): Response
    {
        return $this->render('Front/wishlist.html.twig');
    }

    #[Route('/cart', name: 'cart')]
    public function cart(): Response
    {
        return $this->render('Front/cart.html.twig');
    }

    #[Route('/search', name: 'search')]
    public function search(): Response
    {
        return $this->render('Front/search.html.twig');
    }

    #[Route('/shop', name: 'shop')]
    public function shop(): Response
    {
        return $this->render('Front/shop.html.twig');
    }
    


    #[Route('/shop_detail', name: 'shop_detail')]
    public function shop_detail(): Response
    {
        return $this->render('Front/detail.html.twig');
    }

    #[Route('/checkout', name: 'checkout')]
    public function checkout(): Response
    {
        return $this->render('Front/checkout.html.twig');
    }


}
