<?php

namespace App\Controller;

use App\Entity\Device;
use App\Entity\FCMToken;
use App\Service\DeviceService;
use App\Service\FCMTokenService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/device", name:"device_")]
class DeviceController extends AbstractController{

    private DeviceService $deviceService;

    public function __construct(DeviceService $deviceService)
    {
        $this->deviceService=$deviceService;
    }

    #[Route('/update', name: 'update', methods:["PUT"])]
    public function updateDevice(Request $request): Response
    {
        $token=$request->toArray()["token"] ?? null;

        $this->deviceService->updateDevice($token, $this->getUser());

        return $this->json(["message"=>"OK"]);
    }
}