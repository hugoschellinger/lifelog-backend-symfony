<?php

namespace App\Monolog;

use App\Entity\User;
use Monolog\LogRecord;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Enrichit chaque log avec les informations de l'utilisateur connecté.
 *
 * Ajoute dans "extra.user":
 *  - id
 *  - email
 *  - roles
 */
class UserContextProcessor
{
    public function __construct(
        private Security $security,
    ) {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $user = $this->security->getUser();

        if ($user instanceof User) {
            $record->extra['user'] = [
                'id'    => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ];
        }

        return $record;
    }
}

