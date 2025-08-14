<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class LoginService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager
    ) {
    }

    public function authenticate(string $email, string $password): array
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        
        if (!$user) {
            throw new AuthenticationException('Identifiants invalides.');
        }

        // Vérifier si l'utilisateur est un utilisateur OAuth
        if ($user->isOAuthUser()) {
            throw new AuthenticationException('Cet email est associé à un compte OAuth. Veuillez vous connecter avec votre provider OAuth.');
        }

        // Vérifier si l'utilisateur a un mot de passe
        if (!$user->getPassword()) {
            throw new AuthenticationException('Ce compte n\'a pas de mot de passe configuré.');
        }

        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            throw new AuthenticationException('Identifiants invalides.');
        }

        // Générer le token JWT
        $token = $this->jwtManager->create($user);

        return [
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nom' => $user->getNom(),
                'roles' => $user->getRoles(),
                'avatar' => $user->getAvatar(),
            ]
        ];
    }
} 