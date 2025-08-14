<?php

namespace App\Controller;

use App\Service\LoginService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class LoginController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request, LoginService $loginService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return new JsonResponse([
                'success' => false,
                'errors' => ['Email et mot de passe requis.']
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = $loginService->authenticate($email, $password);
            return new JsonResponse($result, Response::HTTP_OK);
        } catch (AuthenticationException $e) {
            return new JsonResponse([
                'success' => false,
                'errors' => [$e->getMessage()]
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
} 