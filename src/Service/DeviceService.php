<?php

namespace App\Service;

use App\Entity\Device;
use App\Entity\User;
use App\Repository\DeviceRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeviceService extends AbstractService
{
    private TranslatorInterface $translator;
    
    public function __construct(DeviceRepository $DeviceRepository, TranslatorInterface $translator)
    {
        parent::__construct($DeviceRepository);
        $this->translator = $translator;
    }

    public function updateDevice($token, User $user){
        if(!$token){
            throw new BadRequestHttpException($this->translator->trans("FCM Token is empty"));
        }

        $user= $this->findOneBy(["id"=>$user]);
        if(!$user){
            throw new BadRequestHttpException($this->translator->trans("User not found"));
        }

        /** @var FCMToken */
        $fcmToken=$this->findOneBy(["token"=>$token]);

        if(!$fcmToken){
            $fcmToken=new Device();
            $fcmToken->setUser($user);
            $fcmToken->setToken($token);
        }
        $this->save($fcmToken);
    }
}