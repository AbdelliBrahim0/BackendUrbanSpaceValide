<?php

namespace App\Controller;

use App\Service\AdminLoginService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AdminLoginController extends AbstractController
{
    #[Route('/api/admin/login', name: 'api_admin_login', methods: ['POST'])]
    public function login(Request $request, AdminLoginService $adminLoginService): JsonResponse
    {
        // Ensure JSON body
        $contentType = $request->headers->get('Content-Type', '');
        if (!str_contains($contentType, 'application/json')) {
            return new JsonResponse([
                'success' => false,
                'errors' => ['Content-Type doit être application/json']
            ], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse([
                'success' => false,
                'errors' => ['JSON invalide']
            ], Response::HTTP_BAD_REQUEST);
        }

        $username = isset($data['username']) ? (string) $data['username'] : null;
        $password = isset($data['password']) ? (string) $data['password'] : null;

        if (!$username || !$password) {
            return new JsonResponse([
                'success' => false,
                'errors' => ["Nom d'utilisateur et mot de passe requis."]
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = $adminLoginService->authenticate($username, $password);
            return new JsonResponse($result, Response::HTTP_OK);
        } catch (AuthenticationException $e) {
            return new JsonResponse([
                'success' => false,
                'errors' => [$e->getMessage()]
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
}
