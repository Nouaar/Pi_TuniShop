<?php

namespace App\Controller;
use App\Form\CommentaireType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\Commentaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Blog;

class CommentaireController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    #[Route('/comment/{id}/like', name: 'comment_like', methods: ['POST'])]
    public function likeComment(Commentaire $comment, EntityManagerInterface $em): JsonResponse
    {
        $comment->addLike(); // Increment likes
        $em->flush();

        return $this->json(['likes' => $comment->getNbLikes()]);
    }
    // Route to display the comment form and handle submission
    // Route to display the comment form and handle submission
    #[Route('/blog/{id}/add-comment', name: 'blog_comment', methods: ['GET', 'POST'])]
    public function addComment(Request $request, Blog $blog): Response
    {
        $commentaire = new Commentaire();
        $commentaire->setBlog($blog);  // Link the comment to the current blog post

        // Create the form using the CommentaireType form class
        $form = $this->createForm(CommentaireType::class, $commentaire);

        // Handle the form submission
        $form->handleRequest($request);

        // Check if the form is submitted and valid (for POST requests)
        if ($form->isSubmitted() && $form->isValid()) {
            // Set additional fields like likes and creation date
            $commentaire->setNbLikes(0);  // Default likes count
            $commentaire->setDateCreation(new \DateTimeImmutable());
            $commentaire->setUpdateDate(new \DateTime());

            // Persist the comment to the database
            $this->entityManager->persist($commentaire);
            $this->entityManager->flush();

            // Redirect back to the blog page (or blog list page)
            return $this->redirectToRoute('blogs');  // Adjust route as necessary
        }

        // Render the form for GET request or if form is not valid
        return $this->render('blog/add_comment.html.twig', [
            'form' => $form->createView(),
            'blog' => $blog
        ]);
    }
    #[Route('/comment/delete/{id}', name: 'comment_delete', methods: ['POST'])]
    public function delete(EntityManagerInterface $entityManager, int $id): Response
    {
        // Fetch the comment by ID
        $comment = $entityManager->getRepository(Commentaire::class)->find($id);
    
        if (!$comment) {
            throw $this->createNotFoundException('Comment not found');
        }
    
        // Remove the comment from the database
        $entityManager->remove($comment);
        $entityManager->flush();
    
        // Flash message (optional)
        $this->addFlash('success', 'Comment deleted successfully.');
    
        // Redirect back to the comments list
        return $this->redirectToRoute('blog_comments', ['id' => $comment->getBlog()->getId()]);
    }
    
   
}