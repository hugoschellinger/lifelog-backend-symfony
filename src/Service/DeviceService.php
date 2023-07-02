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
    private UserService $userService;
    
    public function __construct(DeviceRepository $DeviceRepository, TranslatorInterface $translator, UserService $userService)
    {
        parent::__construct($DeviceRepository);
        $this->translator = $translator;
        $this->userService = $userService;
    }

    /**
     * On Créer un Device si le FMC Token de l'utilisateur n'est pas trouvé
     *
     * @param [type] $token FCMToken
     * @param User $user Utilisateur qui fait la requête
     * @return void
     */
    public function updateDevice($token, User $user){
        if(!$token){
            throw new BadRequestHttpException($this->translator->trans("FCM Token is empty"));
        }

        $user= $this->userService->findOneBy(["id"=>$user]);
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