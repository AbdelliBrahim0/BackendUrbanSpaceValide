<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LogoutController extends AbstractController
{
    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function logout(): JsonResponse
    {
        // Note: Pour une API stateless avec JWT, la déconnexion se fait côté client
        // en supprimant le token du localStorage/sessionStorage
        
        return new JsonResponse([
            'success' => true,
            'message' => 'Déconnexion réussie. Supprimez le token côté client.'
        ], Response::HTTP_OK);
    }
} 