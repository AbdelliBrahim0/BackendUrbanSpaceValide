<?php

namespace App\Controller;

use App\Service\AdminLoginService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AdminApiLoginController extends AbstractController
{
    #[Route('/api/admin/login', name: 'api_admin_login', methods: ['POST'])]
    public function login(Request $request, AdminLoginService $loginService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;

        if (!$username || !$password) {
            return $this->json([
                'success' => false,
                'message' => 'Nom d\'utilisateur ou mot de passe manquant.'
            ], 400);
        }

        try {
            $result = $loginService->authenticate($username, $password);
            return $this->json($result); // contient success:true + token
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

}