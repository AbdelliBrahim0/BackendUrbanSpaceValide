<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class OAuthService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private JWTTokenManagerInterface $jwtManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function authenticateWithGoogle(string $code): array
    {
        // Configuration Google OAuth
        $clientId = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
        $clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'] ?? '';
        $redirectUri = $_ENV['GOOGLE_REDIRECT_URI'] ?? '';

        if (!$clientId || !$clientSecret) {
            throw new \Exception('Configuration Google OAuth manquante');
        }

        // Échanger le code contre un token d'accès
        $tokenResponse = $this->exchangeCodeForToken($code, $clientId, $clientSecret, $redirectUri);
        $accessToken = $tokenResponse['access_token'];

        // Récupérer les informations utilisateur
        $userInfo = $this->getGoogleUserInfo($accessToken);

        // Créer ou récupérer l'utilisateur
        $user = $this->findOrCreateUser($userInfo, 'google');

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

    public function authenticateWithFacebook(string $accessToken): array
    {
        // Récupérer les informations utilisateur depuis Facebook
        $userInfo = $this->getFacebookUserInfo($accessToken);

        // Créer ou récupérer l'utilisateur
        $user = $this->findOrCreateUser($userInfo, 'facebook');

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

    private function exchangeCodeForToken(string $code, string $clientId, string $clientSecret, string $redirectUri): array
    {
        $httpClient = HttpClient::create();
        
        $response = $httpClient->request('POST', 'https://oauth2.googleapis.com/token', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'code' => $code,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
            ]
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Erreur lors de l\'échange du code contre un token');
        }

        return $response->toArray();
    }

    private function getGoogleUserInfo(string $accessToken): array
    {
        $httpClient = HttpClient::create();
        
        $response = $httpClient->request('GET', 'https://www.googleapis.com/oauth2/v3/userinfo', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ]
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Erreur lors de la récupération des informations utilisateur Google');
        }

        return $response->toArray();
    }

    private function getFacebookUserInfo(string $accessToken): array
    {
        $httpClient = HttpClient::create();
        
        $response = $httpClient->request('GET', 'https://graph.facebook.com/me', [
            'query' => [
                'fields' => 'id,name,email,picture',
                'access_token' => $accessToken,
            ]
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Erreur lors de la récupération des informations utilisateur Facebook');
        }

        $data = $response->toArray();
        
        // Formater les données pour correspondre au format Google
        return [
            'sub' => $data['id'],
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'picture' => $data['picture']['data']['url'] ?? null,
        ];
    }

    private function findOrCreateUser(array $userInfo, string $provider): User
    {
        $providerId = $userInfo['sub'];
        $email = $userInfo['email'] ?? null;
        $name = $userInfo['name'] ?? 'Utilisateur OAuth';
        $avatar = $userInfo['picture'] ?? null;

        // Chercher l'utilisateur par provider ID
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'provider' => $provider,
            'providerId' => $providerId
        ]);

        if (!$user && $email) {
            // Chercher par email si pas trouvé par provider ID
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        }

        if (!$user) {
            // Créer un nouvel utilisateur
            $user = new User();
            $user->setEmail($email ?? $providerId . '@' . $provider . '.oauth');
            $user->setNom($name);
            $user->setProvider($provider);
            $user->setProviderId($providerId);
            $user->setAvatar($avatar);
            $user->setRoles(['ROLE_USER']);
            
            // Champs obligatoires avec des valeurs par défaut
            $user->setTelephone('Non renseigné');
            $user->setAdresse('Non renseignée');
            
            $this->entityManager->persist($user);
        } else {
            // Mettre à jour les informations existantes
            $user->setNom($name);
            $user->setProvider($provider);
            $user->setProviderId($providerId);
            if ($avatar) {
                $user->setAvatar($avatar);
            }
        }

        $this->entityManager->flush();

        return $user;
    }
} 