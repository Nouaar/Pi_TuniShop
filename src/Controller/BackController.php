<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BackController extends AbstractController
{
    #[Route('/back', name: 'app_back')]
    public function index(UtilisateurRepository $utilisateurRepository): Response
    {
        // Fetch all utilisateurs
        $utilisateurs = $utilisateurRepository->findAll();

        // Render back/index.html.twig and pass the utilisateurs data
        return $this->render('back/index.html.twig', [
            'controller_name' => 'BackController',
            'utilisateurs' => $utilisateurs,  // Pass utilisateurs data to the template
        ]);
    }
}
