<?php

namespace App\Controller;

use App\Entity\Blog;
use App\Entity\Utilisateur;
use App\Form\BlogType;
use App\Repository\BlogRepository;
use App\Service\SecurityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/blog')]
final class BlogController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SecurityService $securityService;

    public function __construct(EntityManagerInterface $entityManager, SecurityService $securityService)
    {
        $this->entityManager = $entityManager;
        $this->securityService = $securityService;
    }

    #[Route('/', name: 'app_blog_index', methods: ['GET'])]
    public function index(BlogRepository $blogRepository): Response
    {
        return $this->renderWithAuth('blog/index.html.twig', [
            'blogs' => $blogRepository->findAll(),
   

        ]);
    }

    #[Route('/new', name: 'app_blog_new')]
    public function new(Request $request): Response
    {
        // Récupérer l'utilisateur depuis le cookie
        $userId = $request->cookies->get('user_id');
        $utilisateur = null;

        if ($userId && ctype_digit($userId)) {
            $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->find($userId);
        }

        if (!$utilisateur) {
            $this->addFlash('error', 'Vous devez être connecté pour créer un blog.');
            return $this->redirectToRoute('app_blog_index');
        }

        // Créer un nouvel article de blog
        $blog = new Blog();
        $blog->setUtilisateur($utilisateur);

        // Création du formulaire
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($blog);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_blog_index');
        }

        return $this->renderWithAuth('blog/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_blog_show', methods: ['GET'])]
    public function show(Blog $blog): Response
    {
        return $this->renderWithAuth('blog/show.html.twig', [
            'blog' => $blog,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_blog_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Blog $blog): Response
    {
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return $this->redirectToRoute('app_blog_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderWithAuth('blog/edit.html.twig', [
            'blogs' => $blog,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_blog_delete', methods: ['POST'])]
    public function delete(Request $request, Blog $blog): Response
    {
        if ($this->isCsrfTokenValid('delete' . $blog->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($blog);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_blog_index', [], Response::HTTP_SEE_OTHER);
    }

    private function renderWithAuth(string $template, array $params = []): Response
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $userId = $request->cookies->get('user_id');
        $utilisateur = null;

        if ($userId && ctype_digit($userId)) {
            $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->find($userId);
        }

        $params['isAuthenticated'] = $this->securityService->isUserAuthenticated();
        $params['utilisateur'] = $utilisateur;

        return $this->render($template, $params);
    }
}
