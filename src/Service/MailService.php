<?php

namespace App\Service;

use App\Entity\Mail;
use App\Entity\User;
use App\Repository\MailRepository;
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
        "FORGOT_PASSWORD" => ["subject"=> "Mot de passe oublié","template"=>"forgotPassword"],
        "VERIFICATION_EMAIL" => ["subject"=> "Vérification de l'adresse mail","template"=>"verificationEmail"],
        "REGISTRATION" => ["subject"=> "Bienvenu","template"=>"registration"],
    ];

    private MailerInterface $mailer;


    public function __construct(MailerInterface $mailer,MailRepository $mailRepository)
    {
        parent::__construct($mailRepository);
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

        $this->sendMail($mail, $subject, $template, ["user"=>$user]);
    }


    public function send(string $type, User $user): void
    {
        $mail = (new Mail())
            ->setType($type)
            ->setReceipter($user->getEmail());

        $this->sendMail($mail, $this->MAIL_TYPE_INFO[$type]["subject"], $this->MAIL_TYPE_INFO[$type]["template"], ["user"=>$user]);
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
            throw new ServiceUnavailableHttpException(null,"L'envoi du mail a échoué");
        }finally{
            $this->save($mail);
        }
    }
}
