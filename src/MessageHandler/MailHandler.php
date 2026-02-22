<?php

namespace App\MessageHandler;

use App\Message\Mail;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Psr\Log\LoggerInterface;

#[AsMessageHandler]
class MailHandler
{
    private static string $MAIL_SENDER = 'no-reply@initAPI.fr';

    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(Mail $mail): void
    {

        $email = (new TemplatedEmail())
            ->from(self::$MAIL_SENDER)
            ->to($mail->recipientEmail)
            ->subject($mail->subject)
            ->htmlTemplate("emails/{$mail->template}.html.twig")
            ->context($mail->context);

        $this->mailer->send($email);
    }
}
