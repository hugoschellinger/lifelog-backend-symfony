<?php

namespace App\Service;

use App\Entity\User;
use App\Message\Mail;
use Symfony\Component\Messenger\MessageBusInterface;

class MailService
{
    private static array $MAIL_TYPE_INFO = [
        "FORGOT_PASSWORD" => ["subject" => "Mot de passe oublié", "template" => "forgotPassword"],
        "REGISTRATION" => ["subject" => "Bienvenue !", "template" => "registration"],
    ];

    public function __construct(
        private MessageBusInterface $bus,
    ) {}

    /**
     * Dispatch un mail via le MessageBus
     *
     * @param string $type Type de mail (clé de MAIL_TYPE_INFO)
     * @param User $user Destinataire
     */
    public function send(string $type, User $user): void
    {
        $info = self::$MAIL_TYPE_INFO[$type];

        $mail = new Mail(
            type: $type,
            recipientEmail: $user->getEmail(),
            subject: $info['subject'],
            template: $info['template'],
            context: ['user' => $user],
        );

        $this->bus->dispatch($mail);
    }
}
