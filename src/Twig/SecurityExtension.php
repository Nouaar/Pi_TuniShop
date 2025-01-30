<?php


namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Service\SecurityService;

class SecurityExtension extends AbstractExtension
{
    private SecurityService $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('isAuthenticated', [$this->securityService, 'isUserAuthenticated']),
        ];
    }
}
