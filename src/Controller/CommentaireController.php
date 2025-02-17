<?php

namespace App\Controller;

use App\Entity\Commentaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class CommentaireController extends AbstractController
{
    #[Route('/comment/{id}/like', name: 'comment_like', methods: ['POST'])]
    public function likeComment(Commentaire $comment, EntityManagerInterface $em): JsonResponse
    {
        $comment->addLike(); // Increment likes
        $em->flush();

        return $this->json(['likes' => $comment->getNbLikes()]);
    }
}