<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class VoiceLineController extends AbstractController
{
    #[Route('/get-voiceline/{id}', name: 'get_voiceline')]
    public function getVoiceline(int $id): JsonResponse
    {
        // Example: Fetching audio file URL from a database
        $voicelines = [
            1 => '/audio/Dashboard_switch.mp3',
            2 => '/audio/users_switch.mp3',
            3 => '/audio/products_switch.mp3',
            4 => '/audio/checkouts_switch.mp3',
            5 => '/audio/Depots_stocks_switch.mp3',
            6 => '/audio/reclamations_switch.mp3',
            7 => '/audio/blog_switch.mp3',
            8 => '/audio/consulting_Depots.mp3',
            
            
        ];

        return new JsonResponse(['url' => $voicelines[$id] ?? '']);
    }
}
