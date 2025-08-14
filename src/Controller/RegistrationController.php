<?php

namespace App\Controller;

use App\Service\AuthService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/api/signup', name: 'api_signup', methods: ['POST'])]
    public function register(
        Request $request,
        AuthService $authService,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $res = $authService->newUser($data);

        if(!$res['success']) {
            return new JsonResponse([
                'success' => false,
                'errors' => $res['errors']
            ], Response::HTTP_BAD_REQUEST);
        }

        // Générer le token JWT pour connecter l'utilisateur automatiquement
        $token = $jwtManager->create($res['user']);

        return new JsonResponse([
            'success' => true,
            'message' => 'Utilisateur enregistré avec succès.',
            'token' => $token,
            'user' => [
                'id' => $res['user']->getId(),
                'email' => $res['user']->getEmail(),
                'nom' => $res['user']->getNom(),
                'roles' => $res['user']->getRoles(),
            ]
        ], Response::HTTP_CREATED);
    }
}
