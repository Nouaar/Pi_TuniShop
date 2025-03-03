<?php

namespace App\Controller;
use App\Entity\Commentaire;
use App\Entity\Blog;
use App\Entity\Products;
use App\Form\BlogType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\BlogRepository;
use App\Repository\ProductsRepository;
use App\Service\MailService;

final class BlogController extends AbstractController
{ 

    

    #[Route('/blog', name: 'blog_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Fetch all products (this is fine, depending on your use case)
        $products = $entityManager->getRepository(Products::class)->findAll();
    
        // Fetch all blogs (assuming you have a Blog entity and repository)
        $blogs = $entityManager->getRepository(Blog::class)->findAll();
    
        return $this->render('blog/index.html.twig', [
            'products' => $products,  // Passing products if needed
            'blogs' => $blogs         // Pass the correct blogs variable here
        ]);
    }
    


    #[Route('/blog/{id}/like', name: 'blog_like', methods: ['POST'])]
    public function likeBlog(Blog $blog, EntityManagerInterface $em): JsonResponse
    {
        $blog->addLike();
        $em->flush();

        return $this->json(['likes' => $blog->getNbLikes()]);
    }

   

    #[Route('/blog/add', name: 'blog_add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $blog = new Blog();
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/images';
                
                // Ensure the directory exists
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }

                // Generate a unique filename
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                
                try {
                    // Move the file to the upload directory
                    $imageFile->move($uploadDir, $newFilename);

                    // Store relative path in the database
                    $blog->setImage('uploads/images/' . $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload image.');
                }
            }

            $em->persist($blog);
            $em->flush();

            $this->addFlash('success', 'Blog created successfully!');
            return $this->redirectToRoute('back');
        }

        return $this->render('blog/add.html.twig', ['form' => $form->createView()]);
    }


    #[Route('/blog/delete/{id}', name: 'blog_delete', methods: ['POST'])]
    public function deleteBlog(int $id, EntityManagerInterface $em): Response
    {
        $blog = $em->getRepository(blog::class)->find($id);
    
        if ($blog) {
            $em->remove($blog);
            $em->flush();
        }
        return $this->redirectToRoute('blogs');
    }


//delete blog from back office
#[Route('/back/blog/delete/{id}', name: 'app_blog_delete', methods: ['POST'])]
public function deleteBlogBack(int $id, EntityManagerInterface $em): Response
{
    $blog = $em->getRepository(Blog::class)->find($id);

    if ($blog) {
        // Supprimer d'abord les commentaires liés
        $commentaires = $blog->getCommentsSection();
        foreach ($commentaires as $commentaire) {
            $em->remove($commentaire);
        }

        $em->remove($blog);
        $em->flush();
    }

    return $this->redirectToRoute('back');
}



#[Route('/blog/edit/{id}', name: 'blog_update')]
public function edit(Request $request, Blog $blog, EntityManagerInterface $em  ,  SessionInterface $session): Response
{
    $form = $this->createForm(BlogType::class, $blog);
    $form->handleRequest($request);

    // Récupération des blogs
    $blogs = $em->getRepository(Blog::class)->findAll();
    $cart = $session->get('cart', []);

    if ($form->isSubmitted() && $form->isValid()) {
        /** @var UploadedFile $imageFile */
        $imageFile = $form->get('image')->getData();

        if ($imageFile) {
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/images';

            // Ensure the directory exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            // Generate a unique filename
            $newFilename = uniqid() . '.' . $imageFile->guessExtension();
            
            try {
                // Move the file to the upload directory
                $imageFile->move($uploadDir, $newFilename);

                // Delete the old image (if exists)
                if ($blog->getImage() && file_exists($this->getParameter('kernel.project_dir') . '/public/' . $blog->getImage())) {
                    unlink($this->getParameter('kernel.project_dir') . '/public/' . $blog->getImage());
                }

                // Save the new image path
                $blog->setImage('uploads/images/' . $newFilename);
            } catch (FileException $e) {
                $this->addFlash('error', 'Failed to upload image.');
            }
        }

        $em->flush();

        $this->addFlash('success', 'Blog updated successfully!');
        return $this->redirectToRoute('back');
    }

    // Passer 'blogs' à Twig
    return $this->render('blog/edit.html.twig', [
        'form' => $form->createView(),
        'blog' => $blog,
        'blogs' => $blogs, 
        'isAuthenticated' => $this->getUser() ? true : false, 
        'cart' => $cart
    ]);
}


#[Route('/blog/{id}/comments', name: 'blog_comments')]
public function commentsList(EntityManagerInterface $entityManager, int $id): Response
{
    // Fetch the blog using the provided id
    $blog = $entityManager->getRepository(Blog::class)->find($id);

    if (!$blog) {
        throw $this->createNotFoundException('Blog not found');
    }

    // Fetch only comments related to this blog
    $comments = $entityManager->getRepository(Commentaire::class)->findBy(['blog' => $blog]);

    return $this->render('Back/admin_blog/listComments.html.twig', [
        'comments' => $comments,
        'blog' => $blog, // Pass blog info to the template
    ]);
}
#[Route('/blogs/unliked', name: 'app_blog_unliked')]
public function showUnlikedBlogs(BlogRepository $blogRepository): Response
{
    $unlikedBlogs = $blogRepository->findUnlikedBlogs();

    return $this->render('blog/unliked_blogs.html.twig', [
        'unlikedBlogs' => $unlikedBlogs,
    ]);
}
#[Route('/blog/most-liked', name: 'blog_most_liked')]
public function mostLikedBlog(BlogRepository $blogRepository): Response
{
    $blog = $blogRepository->findMostLikedBlog();

    return $this->render('blog/most_liked.html.twig', [
        'blog' => $blog,
    ]);
}
#[Route('/blog/{id}/like', name: 'blog_like')]
    public function like_Blog(Blog $blog, EntityManagerInterface $em, MailService $mailService)
    {
        $blog->addLike();
        $em->flush();

        // Envoi de l'email
        $mailService->sendLikeNotification('admin@example.com', $blog->getTitre());

        return $this->json(['message' => 'Like ajouté et email envoyé', 'likes' => $blog->getNbLikes()]);
    }

    
}
