<?php

namespace App\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\HttpFoundation\RequestStack;

class SecurityService
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function isUserAuthenticated(): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        $jwtToken = $request?->cookies->get('jwt_token');

        if (!$jwtToken) {
            return false; // No token found, user is not authenticated
        }

        try {
            $key = $_ENV['JWT_SECRET']; // Use environment variable
            JWT::decode($jwtToken, new Key($key, 'HS256'));
            return true;
        } catch (\Firebase\JWT\ExpiredException $e) {
            return false; // Token expired
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return false; // Invalid signature
        } catch (\Exception $e) {
            return false; // Other JWT decoding errors
        }
    }
}
