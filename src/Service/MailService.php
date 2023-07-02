<?php

namespace App\Service;

use App\Entity\Mail;
use App\Entity\User;
use App\Repository\MailRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class MailService extends AbstractService
{
    private $MAIL_SENDER = "no-reply@initAPI.fr";
    private $MAIL_TYPE_INFO = [
        "FORGOT_PASSWORD" => ["subject"=> "Mot de passe oublié","template"=>"forgotPassword"],
        "REGISTRATION" => ["subject"=> "Bienvenu","template"=>"registration"],
    ];

    private MailerInterface $mailer;


    public function __construct(MailerInterface $mailer,MailRepository $mailRepository)
    {
        parent::__construct($mailRepository);
        $this->mailer = $mailer;
    }

    /**
     * Envoie d'un mail possédant un type (pas sûr)
     *
     * @param string $type type de l'email
     * @param User $user Utilisateur qui reçoit le mail
     * @param string $subject Sujet du mail
     * @param string $template Template du mail
     * @return void
     */
    public function sendCustomEmail(string $type, User $user, string $subject, string $template): void
    {

        $mail = (new Mail())
            ->setType($type)
            ->setReceipter($user->getEmail());

        $this->sendMail($mail, $subject, $template, ["user"=>$user]);
    }


    /**
     * Envoie d'un mail
     *
     * @param string $type Type de mail
     * @param User $user Utilisateur qui reçoit le mail
     * @return void
     */
    public function send(string $type, User $user): void
    {
        $mail = (new Mail())
            ->setType($type)
            ->setReceipter($user->getEmail());

        $this->sendMail($mail, $this->MAIL_TYPE_INFO[$type]["subject"], $this->MAIL_TYPE_INFO[$type]["template"], ["user"=>$user]);
    }

    /**
     * Fonction qui envoie réellement le mail
     *
     * @param Mail $mail Entité mail 
     * @param string $subject Sujet du mail
     * @param string $template Template du mail
     * @param array $context Context du mail
     * @return void
     */
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
