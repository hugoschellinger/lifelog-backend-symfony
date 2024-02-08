<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\DeviceService;
use App\Service\SecurityService;
use App\Service\UserService;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/user", name:"user_")]
class UserController extends CrudAbstractController{

    private TranslatorInterface $translator;
    private UserService $userService;
    private DeviceService $deviceService;

    public function __construct(UserService $service,TranslatorInterface $translator, DeviceService $deviceService)
    {
        parent::__construct(User::class,$service,["groups" => "User:read"],["firstname", "lastname"]);
        $this->translator=$translator;
        $this->userService=$service;
        $this->deviceService=$deviceService;
    }

    #[Route('/verification', name: 'verification_email', methods:["POST"])]
    public function verificationEmail(Request $request): Response
    {
        $code=$request->toArray()["code"] ?? null;
        $email=$request->toArray()["email"] ?? null;

        $this->userService->verificationEmail($email, $code);

        return $this->json(["message"=>"OK"],200);
    }

    #[Route('/sendVerificationEmail', name: 'send_verification_email', methods:["POST"])]
    public function sendVerificationEmail(Request $request, SecurityService $securityService): Response
    {
        $securityService->sendVerificationEmail($this->getUser());

        return $this->json(["message"=>"OK"],200);
    }

    #[Route("/password",name:"modify_password",methods:["PATCH"])]
    public function setPassword(Request $request,UserPasswordHasherInterface $hasher){

        $password=$request->toArray()["password"];
        $newPassword=$request->toArray()["newPassword"];
        
        $user=$this->service->findOneBy(["email" => $this->getUser()->getUserIdentifier()]);

        if(!$hasher->isPasswordValid($user,$password)){
            throw new BadRequestException($this->translator->trans("password invalid"));
        }

        $hashedPassword=$hasher->hashPassword($user,$newPassword);
        $user->setPassword($hashedPassword);
        $this->service->save($user);

        return $this->json(["message"=>"OK"],204);
    }

    #[Route("/logout",name:"logout",methods:["POST"])]
    public function logout(Request $request){

        $deviceToken= $request->toArray()["device"] ?? null;

        // if(!$deviceToken){
        //     throw new BadRequestHttpException($this->translator->trans("Device token is empty"));
        // }

        /** @var User */
        $user = $this->userService->findOneBy(["id" => $this->getUser()]);

        if(!$user){
            throw new BadRequestException($this->translator->trans("user no connected"));
        }

        $device = $this->deviceService->findOneBy(["token" => $deviceToken]);

        if($device){
            $this->deviceService->delete($device);
        }

        return $this->json(["message"=>"OK"],200);
    }
}