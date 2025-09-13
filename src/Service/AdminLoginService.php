<?php

namespace App\Service;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AdminLoginService
{
    private $entityManager;
    private $jwtManager;
    private $tokenStorage;

    public function __construct(
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $jwtManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->entityManager = $entityManager;
        $this->jwtManager = $jwtManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function authenticate(string $username, string $password): array
    {
        $admin = $this->entityManager->getRepository(Admin::class)->findOneBy(['username' => $username]);

        if (!$admin) {
            throw new AuthenticationException('Nom d\'utilisateur incorrect.');
        }

        if (!password_verify($password, $admin->getPassword())) {
            throw new AuthenticationException('Mot de passe incorrect.');
        }

        $token = new UsernamePasswordToken($admin, 'main', $admin->getRoles());
        $this->tokenStorage->setToken($token);

        $jwt = $this->jwtManager->create($admin);

        return [
            'success' => true,
            'token' => $jwt,
        ];
    }
}