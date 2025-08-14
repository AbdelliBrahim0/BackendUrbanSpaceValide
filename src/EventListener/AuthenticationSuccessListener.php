<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use App\Entity\User;
use App\Entity\Admin;

final class AuthenticationSuccessListener
{
    #[AsEventListener(event: 'lexik_jwt_authentication.on_authentication_success')]
    public function onLexikJwtAuthenticationOnAuthenticationSuccess($event): void
    {
        $data = $event->getData();
        $user = $event->getUser();
        if ($user instanceof User) {
            $data['user'] = [
                'type' => 'user',
                'nom' => $user->getNom(),
                'email' => $user->getEmail(),
                'telephone' => $user->getTelephone(),
                'adresse' => $user->getAdresse(),
                'roles' => $user->getRoles(),
            ];
        } elseif ($user instanceof Admin) {
            $data['user'] = [
                'type' => 'admin',
                'username' => $user->getUserIdentifier(),
                'roles' => $user->getRoles(),
            ];
        }

        $event->setData($data);
    }
}
