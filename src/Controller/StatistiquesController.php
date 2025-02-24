<?php

namespace App\Controller;

use App\Repository\CheckoutRepository;
use App\Repository\ReclamationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Remboursement;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Reclamation;
use Doctrine\ORM\EntityManagerInterface;

class StatistiquesController extends AbstractController
{
    #[Route('/statistiques', name: 'admin_statistiques')]
    public function index()
    {
        return $this->render('back/statistique.html.twig', [
            'isAuthenticated' => $this->getUser() ? true : false, // Vérifie si un utilisateur est connecté
        ]);
    }
    
    #[Route('/statistiques/data', name: 'admin_statistiques_data')]
    public function getStatistiquesData(ReclamationRepository $reclamationRepo, CheckoutRepository $checkoutRepo)
    {
        // Récupérer le nombre de réclamations
        $nombreReclamations = $reclamationRepo->count([]);
    
        // Récupérer le nombre de produits achetés (nombre de commandes)
        $nombreCommandes = $checkoutRepo->count([]);
    
        // Retourner les données en format JSON
        return new JsonResponse([
            'reclamations' => $nombreReclamations, // Nombre de réclamations
            'commandes' => $nombreCommandes, // Nombre de commandes
        ]);
    }

    
  
    
    
}
