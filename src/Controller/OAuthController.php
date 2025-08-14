<?php

namespace App\Controller;

use App\Service\OAuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OAuthController extends AbstractController
{
    #[Route('/api/oauth/google/authenticate', name: 'api_oauth_google_authenticate', methods: ['POST'])]
    public function authenticateWithGoogle(Request $request, OAuthService $oauthService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $code = $data['code'] ?? null;

        if (!$code) {
            return new JsonResponse([
                'success' => false,
                'errors' => ['Code d\'autorisation requis.']
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = $oauthService->authenticateWithGoogle($code);
            return new JsonResponse($result, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'errors' => [$e->getMessage()]
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/api/oauth/facebook/authenticate', name: 'api_oauth_facebook_authenticate', methods: ['POST'])]
    public function authenticateWithFacebook(Request $request, OAuthService $oauthService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $accessToken = $data['access_token'] ?? null;

        if (!$accessToken) {
            return new JsonResponse([
                'success' => false,
                'errors' => ['Token d\'accès requis.']
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = $oauthService->authenticateWithFacebook($accessToken);
            return new JsonResponse($result, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'errors' => [$e->getMessage()]
            ], Response::HTTP_BAD_REQUEST);
        }
    }
} 