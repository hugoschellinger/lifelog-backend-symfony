<?php

namespace App\Service;

use App\Entity\Mail;
use App\Entity\User;
use EmailType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Mailer\Exception\HttpTransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class MailService extends AbstractService
{
    private $MAIL_SENDER = "no-reply@initAPI.fr";
    private $MAIL_TYPE_INFO = [
        EmailType::FORGOT_PASSWORD => ["subject"=> "Mot de passe oublié","template"=>"forgotPassword"],
        EmailType::VERIFICATION_EMAIL => ["subject"=> "Vérification de l'adresse mail","template"=>"verificationEmail"],
        EmailType::REGISTRATION => ["subject"=> "Bienvenu","template"=>"registration"],
    ];

    private MailerInterface $mailer;


    public function __construct(MailerInterface $mailer,)
    {
        $this->mailer = $mailer;
    }

    /**
     * Custom Email
     */
    public function sendCustomEmail(string $type, User $user, string $subject, string $template, array $context): void
    {

        $mail = (new Mail())
            ->setType($type)
            ->setReceipter($user->getEmail());

        $this->sendMail($mail, $subject, $template, compact($user));
    }

    /**
     * saved Email
     */
    public function send(EmailType $type, User $user): void
    {
        dump($type);
        $mail = (new Mail())
            ->setType($type::class)
            ->setReceipter($user->getEmail());


        $this->sendMail($mail, $this->MAIL_TYPE_INFO[$type]["subject"], $this->MAIL_TYPE_INFO[$type::class]["template"], compact($user));
    }

    private function sendMail(Mail $mail, string $subject, string $template, array $context)
    {
        try {
            $email = (new TemplatedEmail())
                ->from($this->MAIL_SENDER)
                ->to($mail->getReceipter())
                ->subject($subject)
                ->htmlTemplate("emails/$template.html.twig")
                ->context($context);

            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $mail->setIsSended(false);
            throw new ServiceUnavailableHttpException("L'envoi du mail a échoué");
        }
        $this->save($mail);
    }
}
