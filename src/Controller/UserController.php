<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;

class UserController extends AbstractController
{
    #[Route('/api/user/profile', name: 'api_user_profile', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getProfile(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return new JsonResponse([
            'success' => true,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nom' => $user->getNom(),
                'telephone' => $user->getTelephone(),
                'adresse' => $user->getAdresse(),
                'roles' => $user->getRoles(),
                'avatar' => $user->getAvatar(),
                'provider' => $user->getProvider(),
                'isOAuthUser' => $user->isOAuthUser(),
                'dateInscription' => $user->getDateInscription()->format('Y-m-d H:i:s'),
            ]
        ], Response::HTTP_OK);
    }

    #[Route('/api/user/update-profile', name: 'api_user_update_profile', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function updateProfile(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        // Mise à jour des champs autorisés
        if (isset($data['nom'])) {
            $user->setNom($data['nom']);
        }
        if (isset($data['telephone'])) {
            $user->setTelephone($data['telephone']);
        }
        if (isset($data['adresse'])) {
            $user->setAdresse($data['adresse']);
        }

        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Profil mis à jour avec succès',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nom' => $user->getNom(),
                'telephone' => $user->getTelephone(),
                'adresse' => $user->getAdresse(),
                'roles' => $user->getRoles(),
                'avatar' => $user->getAvatar(),
                'provider' => $user->getProvider(),
                'isOAuthUser' => $user->isOAuthUser(),
            ]
        ], Response::HTTP_OK);
    }

    #[Route('/api/user/check-auth', name: 'api_user_check_auth', methods: ['GET'])]
    public function checkAuth(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'authenticated' => false,
                'message' => 'Utilisateur non connecté'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'success' => true,
            'authenticated' => true,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nom' => $user->getNom(),
                'roles' => $user->getRoles(),
            ]
        ], Response::HTTP_OK);
    }
} 