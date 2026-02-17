<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/test', name: 'test')]
class TestController extends AbstractController
{
    public function __construct()
    {
    }

    #[Route('/test', name: 'test', methods: ["POST"])]
    public function test(): Response
    {

        return $this->json("OK", 200);
    }
}
