<?php
namespace App\Controller;

use App\Repository\BlogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiStatController extends AbstractController
{
    #[Route('/stat', name: 'statistique')]
    public function statistiques(BlogRepository $blogRepository): Response
    {
        // Fetch all blogs with their likes and comments count
        $blogs = $blogRepository->createQueryBuilder('b')
            ->select('b.id, b.titre, b.nb_likes, COUNT(c.id) AS nb_comments')
            ->leftJoin('b.comments_section', 'c') // Join avec la table des commentaires
            ->groupBy('b.id')
            ->getQuery()
            ->getResult();

        return $this->render('api_stat/statistique.html.twig', [
            'blogs' => $blogs // Send all blog stats to Twig
        ]);
    }
}
