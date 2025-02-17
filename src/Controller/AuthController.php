<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UtilisateurRepository;
use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class AuthController extends AbstractController
{
    #[Route('/login', name: 'app_utilisateur_login', methods: ['GET', 'POST'])]
    public function login(
        Request $request,
        UtilisateurRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        if ($request->isMethod('POST')) {
            // Validate CSRF token
            $csrfToken = $request->request->get('_csrf_token');
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('authenticate', $csrfToken))) {
                $this->addFlash('error', 'Invalid CSRF token.');
                return $this->redirectToRoute('app_utilisateur_login');
            }

            $email = $request->request->get('email');
            $password = $request->request->get('mot_de_passe');
            $utilisateur = $userRepository->findOneBy(['email' => $email]);

            if (!$utilisateur) {
                $this->addFlash('error', 'User not found');
                return $this->redirectToRoute('app_utilisateur_login'); 
            }

            // Verify password
            if ($passwordHasher->isPasswordValid($utilisateur, $password)) {
                // Create JWT token
                $key = 'your_secret_key'; 
                $payload = [
                    'id' => $utilisateur->getId(),
                    'email' => $utilisateur->getEmail(),
                    'role' => $utilisateur->getRole(),
                    'iat' => time(),
                    'exp' => time() + 3600,
                ];
                $jwt = JWT::encode($payload, $key, 'HS256');

                // Create cookies
                $jwtCookie = new Cookie('jwt_token', $jwt, time() + 3600, '/', null, true, true, false);
                $idCookie = new Cookie('user_id', (string) $utilisateur->getId(), time() + 3600, '/', null, true, true, false);

                // Redirect based on role
                $response = in_array('ROLE_ADMIN', $utilisateur->getRoles()) 
                    ? $this->redirectToRoute('back')
                    : $this->redirectToRoute('home');

                $response->headers->setCookie($jwtCookie);
                $response->headers->setCookie($idCookie);

                $this->addFlash('success', 'User logged in successfully');
                return $response;
            } else {
                $this->addFlash('error', 'Invalid email or password');
                return $this->redirectToRoute('app_utilisateur_login'); 
            }
        }

        return $this->render('Front/SignUp_LogIn_Form.html.twig'); 
    }

    #[Route('/register', name: 'app_utilisateur_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        UtilisateurRepository $userRepository,
        UserPasswordHasherInterface $passwordEncoder,
        EntityManagerInterface $entityManager,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        if ($request->isMethod('POST')) {
            // Validate CSRF token
            $csrfToken = $request->request->get('_csrf_token');
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('register', $csrfToken))) {
                $this->addFlash('error', 'Invalid CSRF token.');
                return $this->redirectToRoute('app_utilisateur_register');
            }

            $prenom = $request->request->get('prenom');
            $nom = $request->request->get('nom');
            $email = $request->request->get('email');
            $password = $request->request->get('mot_de_passe');

            if ($userRepository->findOneBy(['email' => $email])) {
                $this->addFlash('error', 'Cet email est déjà utilisé');
                return $this->redirectToRoute('app_utilisateur_register');
            }

            // Create new user
            $utilisateur = new Utilisateur();
            $utilisateur->setPrenom($prenom);
            $utilisateur->setNom($nom);
            $utilisateur->setEmail($email);
            $utilisateur->setPassword($passwordEncoder->hashPassword($utilisateur, $password));
            $utilisateur->setRole('ROLE_USER');

            $entityManager->persist($utilisateur);
            $entityManager->flush();

            $this->addFlash('success', 'Inscription réussie');
            return $this->redirectToRoute('app_utilisateur_login');
        }

        return $this->render('Front/SignUp_LogIn_Form.html.twig');
    }
}
