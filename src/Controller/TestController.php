<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\RateLimiter\Annotation\RateLimiter;

#[Route('/test', name: 'test')]
class TestController extends AbstractController
{
    public function __construct()
    {
    }

    #[Route('/test', name: 'test', methods: ["POST"])]
    #[RateLimiter('test')]
    public function test(): Response
    {
        return $this->json("OK", 200);
    }
}
