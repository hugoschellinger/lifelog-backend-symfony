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
    private TranslatorInterface $translator;
    private UserService $userService;

    public function __construct(DeviceService $deviceService, TranslatorInterface $translator, UserService $userService)
    {
        $this->deviceService=$deviceService;
        $this->translator=$translator;
        $this->userService=$userService;
    }

    #[Route('/update', name: 'update', methods:["PUT"])]
    public function updateDevice(Request $request): Response
    {
        $token=$request->toArray()["token"] ?? null;

        if(!$token){
            throw new BadRequestHttpException($this->translator->trans("FCM Token is empty"));
        }

        $user= $this->userService->findOneBy(["id"=>$this->getUser()]);
        if(!$user){
            throw new BadRequestHttpException($this->translator->trans("User not found"));
        }

        /** @var FCMToken */
        $fcmToken=$this->deviceService->findOneBy(["token"=>$token]);

        if(!$fcmToken){
            $fcmToken=new Device();
            $fcmToken->setUser($user);
            $fcmToken->setToken($token);
        }
        $this->deviceService->save($fcmToken);
        return $this->json(["message"=>"OK"]);
    }
}