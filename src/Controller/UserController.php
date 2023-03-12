<?php

namespace App\Controller;

use App\Service\UserService;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/api/user", name:"user_")]
class UserController extends CrudAbstractController{

    private TranslatorInterface $translator;

    public function __construct(UserService $service,TranslatorInterface $translator)
    {
        parent::__construct(User::class,$service,["groups" => "User:read"],["firstname", "lastname"]);
        $this->translator=$translator;
    }

    #[Route('/verification', name: 'verification_email', methods:["GET"])]
    public function verificationEmail(Request $request): Response
    {
        $token=$request->get("token") ?? null;

        if(!$token){
            throw new BadRequestHttpException($this->translator->trans("Token is empty"));
        }

        /** @var User */
        $user = $this->service->findOneBy(["verificationToken" => $token]);

        if(!$user){
            throw new BadRequestHttpException($this->translator->trans("Link is wrong"));
        }

        $user->setVerificationToken(null);

        $this->service->save($user);

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
}