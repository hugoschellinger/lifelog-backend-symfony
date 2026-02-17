<?php

namespace App\Controller\Views;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/email', name: 'email_')]
class EmailController extends AbstractController
{
    #[Route('/forgotPassword', name: 'forgotPassword', methods: ['GET'])]
    public function forgotPassword(): Response
    {
        return $this->render('emails/forgotPassword.html.twig',[
            "user" => [
                "passwordToken" => '1234567890',
                "firstname" => "John",
                "lastname" => "Doe",
            ]
        ]);
    }

    #[Route('/registration', name: 'registration', methods: ['GET'])]
    public function registration(): Response
    {
        return $this->render('emails/registration.html.twig',[
            "user" => [
                "verificationToken" => '1234567890',
                "firstname" => "John",
                "lastname" => "Doe",            
            ]
        ]);
    }
}
