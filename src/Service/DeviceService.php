<?php

namespace App\Service;

use App\Entity\Device;
use App\Entity\User;
use App\Repository\DeviceRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeviceService extends AbstractService
{
    private TranslatorInterface $translator;
    private UserService $userService;
    
    public function __construct(DeviceRepository $DeviceRepository, TranslatorInterface $translator, UserService $userService)
    {
        parent::__construct($DeviceRepository);
        $this->translator = $translator;
        $this->userService = $userService;
    }

    public function updateDevice($token, UserInterface $user){
        if(!$token){
            throw new BadRequestHttpException($this->translator->trans("FCM Token is empty"));
        }

        $user= $this->userService->findOneBy(["id"=>$user]);
        if(!$user){
            throw new BadRequestHttpException($this->translator->trans("User not found"));
        }

        /** @var FCMToken */
        $fcmToken=$this->findOneBy(["id"=>$user]);

        if(!$fcmToken){
            $fcmToken=new Device();
            $fcmToken->setUser($user);
            $fcmToken->setToken($token);
        }
        $this->save($fcmToken);
    }
}