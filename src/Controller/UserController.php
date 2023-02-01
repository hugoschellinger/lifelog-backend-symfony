<?php

namespace App\Controller;

use App\Service\UserService;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/api/user", name:"user_")]
class UserController extends CrudAbstractController{

    private TranslatorInterface $translator;

    public function __construct(UserService $service,TranslatorInterface $translator)
    {
        parent::__construct(User::class,$service,["User:modify"]);
        $this->translator=$translator;
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