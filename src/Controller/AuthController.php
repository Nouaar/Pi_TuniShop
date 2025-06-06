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
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

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
    
            // Check if the user's email is verified
            if ($utilisateur->getVerificationToken() !== null) {
                $this->addFlash('error', 'Your email is not verified. Please check your inbox.');
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
                if (in_array('ROLE_ADMIN', $utilisateur->getRoles())) {
                    $response = $this->redirectToRoute('back');
                } elseif (in_array('ROLE_SELLER', $utilisateur->getRoles())) {
                    $response = $this->redirectToRoute('product_add'); // Redirect to seller page
                } else {
                    $response = $this->redirectToRoute('home');
                }
    
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
        CsrfTokenManagerInterface $csrfTokenManager,
        MailerInterface $mailer,
        UrlGeneratorInterface $urlGenerator
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
            $role = $request->request->get('role'); 
    
            // Check if the email already exists
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
            $utilisateur->setRole($role);

            // Generate and set verification token
            $verificationToken = bin2hex(random_bytes(32)); // Generate a random token
            $utilisateur->setVerificationToken($verificationToken);

            $entityManager->persist($utilisateur);
            $entityManager->flush();

            // Send email with verification link
            $verificationUrl = $urlGenerator->generate('app_utilisateur_verify', [
                'token' => $verificationToken,
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            $emailMessage = (new Email())
                ->from('no-reply@yourdomain.com')
                ->to($email)
                ->subject('Email Verification')
                ->text('Please verify your email by clicking the following link: ' . $verificationUrl);

            $mailer->send($emailMessage);

            $this->addFlash('success', 'Inscription réussie. Veuillez vérifier votre email.');
            return $this->redirectToRoute('app_utilisateur_login');
        }
    
        return $this->render('Front/SignUp_LogIn_Form.html.twig');
    }

    #[Route('/verify/{token}', name: 'app_utilisateur_verify', methods: ['GET'])]
    public function verifyEmail(
        string $token,
        UtilisateurRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $utilisateur = $userRepository->findOneBy(['verificationToken' => $token]);

        if (!$utilisateur) {
            $this->addFlash('error', 'Invalid or expired verification token.');
            return $this->redirectToRoute('app_utilisateur_login');
        }

        // Clear the verification token and activate the account
        $utilisateur->setVerificationToken(null);
        $entityManager->flush();

        $this->addFlash('success', 'Email verified successfully. You can now log in.');
        return $this->redirectToRoute('app_utilisateur_login');
    }





    #[Route('/forgot-password', name: 'app_forgot_password', methods: ['POST'])]
    public function forgotPassword(
        Request $request,
        UtilisateurRepository $userRepository,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
    
        if (!$email) {
            return new JsonResponse(['success' => false, 'message' => 'Email is required'], Response::HTTP_BAD_REQUEST);
        }
    
        $utilisateur = $userRepository->findOneBy(['email' => $email]);
    
        if (!$utilisateur) {
            return new JsonResponse(['success' => false, 'message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
    
        // Generate verification code
        $verificationCode = rand(100000, 999999);
        $utilisateur->setVerificationToken($verificationCode);
    
        $entityManager->flush();
    
        // Send email
        $emailMessage = (new Email())
            ->from('no-reply@yourdomain.com')
            ->to($email)
            ->subject('Password Reset Verification Code')
            ->text("Your verification code is: $verificationCode");
    
        $mailer->send($emailMessage);
    
        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }
    




    #[Route('/reset-password', name: 'app_reset_password', methods: ['POST'])]
    public function resetPassword(
        Request $request,
        UtilisateurRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $verificationCode = $data['verification_code'] ?? null;
        $newPassword = $data['new_password'] ?? null;
    
        if (!$email || !$verificationCode || !$newPassword) {
            return new JsonResponse(['error' => 'Missing fields'], Response::HTTP_BAD_REQUEST);
        }
    
        $utilisateur = $userRepository->findOneBy(['email' => $email]);
    
        if (!$utilisateur) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
    
        if ($utilisateur->getVerificationToken() !== $verificationCode) {
            return new JsonResponse(['error' => 'Invalid verification code'], Response::HTTP_BAD_REQUEST);
        }
    
        // Reset password
        $hashedPassword = $passwordHasher->hashPassword($utilisateur, $newPassword);
        $utilisateur->setPassword($hashedPassword);
        $utilisateur->setVerificationToken(null); // Clear the verification code
    
        $entityManager->flush();
    
        return new JsonResponse(['success' => 'Password has been reset successfully.'], Response::HTTP_OK);
    }
    





}
