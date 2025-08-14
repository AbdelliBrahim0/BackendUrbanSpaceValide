<?php

namespace App\Controller;

use App\Entity\Admin;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin')]
class AdminController extends AbstractController
{
    #[Route('/me', name: 'api_admin_me', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function me(): JsonResponse
    {
        /** @var Admin $admin */
        $admin = $this->getUser();

        return new JsonResponse([
            'success' => true,
            'admin' => [
                'id' => method_exists($admin, 'getId') ? $admin->getId() : null,
                'username' => $admin->getUserIdentifier(),
                'roles' => $admin->getRoles(),
            ]
        ], Response::HTTP_OK);
    }
}
