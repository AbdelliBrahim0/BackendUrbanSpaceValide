<?php

namespace App\Service;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AdminLoginService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {}

    /**
     * @return array{success: bool, token?: string, admin?: array{id: int|null, username: string, roles: array<string>}}
     */
    public function authenticate(string $username, string $password): array
    {
        $admin = $this->entityManager->getRepository(Admin::class)->findOneBy(['username' => strtolower($username)]);
        if (!$admin) {
            throw new AuthenticationException('Identifiants invalides.');
        }

        if (!$admin->getPassword()) {
            throw new AuthenticationException('Ce compte administrateur n\'a pas de mot de passe configuré.');
        }

        if (!$this->passwordHasher->isPasswordValid($admin, $password)) {
            throw new AuthenticationException('Identifiants invalides.');
        }

        $token = $this->jwtManager->create($admin);

        return [
            'success' => true,
            'token' => $token,
            'admin' => [
                'id' => method_exists($admin, 'getId') ? $admin->getId() : null,
                'username' => $admin->getUserIdentifier(),
                'roles' => $admin->getRoles(),
            ],
        ];
    }
}
