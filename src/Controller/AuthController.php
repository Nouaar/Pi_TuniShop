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





final class AuthController extends AbstractController
{
    #[Route('/login', name: 'app_utilisateur_login', methods: ['GET', 'POST'])]
    public function login(Request $request, UtilisateurRepository $userRepository, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('mot_de_passe');
            $utilisateur = $userRepository->findOneBy(['email' => $email]);
    
            if (!$utilisateur) {
                $this->addFlash('error', 'User not found');
                return $this->redirectToRoute('app_utilisateur_login'); 
            }

            // Get the hashed password stored in the database
            $storedPasswordHash = $utilisateur->getPassword();

            // Verify the entered password against the stored hash
            if ($passwordHasher->isPasswordValid($utilisateur, $password)) {
                // Password is correct, proceed with login
                
                // Create a JWT token for the user
                $key = 'your_secret_key'; // Replace with a proper key
                $payload = [
                    'id' => $utilisateur->getId(),
                    'email' => $utilisateur->getEmail(),
                    'role' => $utilisateur->getRole(),
                    'iat' => time(), // Issued at: current timestamp
                    'exp' => time() + 3600, // Expiration: 1 hour
                ];
                $jwt = JWT::encode($payload, $key, 'HS256');
    
                // Create cookies for both the JWT token and user ID
                $jwtCookie = new Cookie(
                    'jwt_token',           // Name of the JWT token cookie
                    $jwt,                  // Value of the JWT token
                    time() + 3600,         // Expiration time (1 hour)
                    '/',                   // Path for which the cookie is valid
                    null,                  // Domain (null means current domain)
                    true,                  // Secure: set to true if using HTTPS
                    true,                  // HttpOnly: ensures the cookie is only sent over HTTP(S), not accessible via JavaScript
                    false                  // SameSite attribute (use 'Strict' or 'Lax' if needed)
                );
    
                $idCookie = new Cookie(
                    'user_id',             // Name of the user ID cookie
                    (string) $utilisateur->getId(), // Value of the user ID
                    time() + 3600,         // Expiration time (1 hour)
                    '/',                   // Path for which the cookie is valid
                    null,                  // Domain (null means current domain)
                    true,                  // Secure: set to true if using HTTPS
                    true,                  // HttpOnly
                    false                  // SameSite
                );
    
                // Redirect to home and set the cookies
                $response = $this->redirectToRoute('home');
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
    public function register(Request $request, UtilisateurRepository $userRepository, UserPasswordHasherInterface $passwordEncoder, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $prenom = $request->request->get('prenom');
            $nom = $request->request->get('nom');
            $email = $request->request->get('email');
            $password = $request->request->get('mot_de_passe');

            // Check if the email already exists
            $existingUser = $userRepository->findOneBy(['email' => $email]);
            if ($existingUser) {
                $this->addFlash('error', 'Cet email est déjà utilisé');
                return $this->redirectToRoute('app_utilisateur_register');
            }

            // Create a new user entity
            $utilisateur = new Utilisateur();
            $utilisateur->setPrenom($prenom);
            $utilisateur->setNom($nom);
            $utilisateur->setEmail($email);
            $utilisateur->setPassword($passwordEncoder->hashPassword($utilisateur, $password));
            $utilisateur->setRole('ROLE_USER'); // You can set a default role

            // Persist the user to the database
            $entityManager->persist($utilisateur);
            $entityManager->flush();

            // Add a success flash message
            $this->addFlash('success', 'Inscription réussie');

            // Redirect to login page
            return $this->redirectToRoute('app_utilisateur_login');
        }

        return $this->render('Front/SignUp_LogIn_Form.html.twig');
    }






}
