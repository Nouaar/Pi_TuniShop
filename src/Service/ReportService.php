<?php

namespace App\Service;

use App\Repository\ReclamationRepository;
use App\Repository\RemboursementRepository;

class ReportService
{
    private $reclamationRepository;
    private $remboursementRepository;

    public function __construct(ReclamationRepository $reclamationRepository, RemboursementRepository $remboursementRepository)
    {
        $this->reclamationRepository = $reclamationRepository;
        $this->remboursementRepository = $remboursementRepository;
    }

    public function generateReport(): array
    {
        // Récupérer les réclamations et remboursements depuis les repositories
        $reclamations = $this->reclamationRepository->findAll();
        $remboursements = $this->remboursementRepository->findAll();
    
        // Calculer le total des réclamations et remboursements
        $totalReclamations = count($reclamations);
        $totalRemboursements = count($remboursements);
    
        // Calculer les montants minimum et maximum des remboursements
        $montantMin = $remboursements ? min(array_map(fn($r) => $r->getMontant(), $remboursements)) : 0;
        $montantMax = $remboursements ? max(array_map(fn($r) => $r->getMontant(), $remboursements)) : 0;
    
        // Créer le texte du rapport
        $rapportTexte = "You have received $totalReclamations claims and processed $totalRemboursements refunds, with amounts ranging between $montantMin and $montantMax.";
    
        // Générer les réclamations par raison
        $reclamationsParRaison = [];
        foreach ($reclamations as $reclamation) {
            $raison = $reclamation->getRaison();
            if (!isset($reclamationsParRaison[$raison])) {
                $reclamationsParRaison[$raison] = 0;
            }
            $reclamationsParRaison[$raison]++;
        }
    
        // Retourner toutes les données nécessaires
        return [
            'reclamations' => $reclamationsParRaison, // Assurez-vous que cette ligne est présente
            'remboursements' => $remboursements,
            'rapportTexte' => $rapportTexte,
            'totalReclamations' => $totalReclamations,
            'totalRemboursements' => $totalRemboursements,
            'montantMin' => $montantMin,
            'montantMax' => $montantMax,
        ];
    }
}
