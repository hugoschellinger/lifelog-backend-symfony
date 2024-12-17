<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\FireBaseService;
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
    public function test(UserService $userService, FireBaseService $FireBaseService): Response
    {
        /** @var User */
        $user=$userService->findOneBy(["id"=>$this->getUser()]);
        $FireBaseService->sendNotification($user,"Test","Test de notification");

        return $this->json("OK", 200);
    }


    #[Route('/pushNotification', name: 'push_notification', methods: ["GET"])]
    public function testNotificationPush(UserService $userService, FireBaseService $FireBaseService): Response
    {
        /** @var User */
        $user=$userService->findOneBy(["id"=>$this->getUser()]);
        $FireBaseService->sendNotification($user,"Test","Test de notification");

        return $this->json("OK", 200);
    }
}
