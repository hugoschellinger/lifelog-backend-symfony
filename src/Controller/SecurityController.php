<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\MailService;
use App\Service\UserService;
use DateTimeImmutable;
use EmailType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api', name: 'api_')]
class SecurityController extends AbstractController
{
    private UserService $userService;
    private MailService $mailService;
    private TranslatorInterface $translator;
    private TokenGeneratorInterface $tokenGenerator;

    public function __construct(UserService $userService, MailService $mailService,TranslatorInterface $translator,TokenGeneratorInterface $tokenGenerator)
    {
        $this->userService = $userService;
        $this->mailService = $mailService;
        $this->translator = $translator;
        $this->tokenGenerator = $tokenGenerator;
    }

    #[Route('/test', name: 'test',methods:["GET"])]
    public function test(Request $request): Response
    {
        return $this->json("OK", 200);
    }

    #[Route('/register', name: 'register',methods:["POST"])]
    public function registration(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $email = $request->toArray()["email"];
        $password = $request->toArray()["password"];
        $firstname = $request->toArray()["firstname"];
        $lastname = $request->toArray()["lastname"];

        $alreadyRegisted = $this->userService->findOneBy(["email" => $email]);

        if ($alreadyRegisted) {
            throw new BadRequestHttpException($this->translator->trans("Email already used"));
        }

        $user = new User();
        $user->setEmail($email);
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $password
        );
        $user->setPassword($hashedPassword);
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        
        $this->mailService->send(EmailType::REGISTRATION ,$user);

        $this->userService->save($user, true);

        return $this->json($user, 201);
    }

    #[Route('/resetPassword', name: 'reset_password',methods:["POST"])]
    public function resetPassword(Request $request): Response
    {
        $email=$request->toArray()["email"] ?? null;

        if(!$email){
            throw new BadRequestHttpException($this->translator->trans("Email is empty"));
        }

        $user=$this->userService->findOneBy(["email"=>$email]);

        if($user){
            $user->setPasswordToken($this->tokenGenerator->generateToken());
            $user->setResetAt(new DateTimeImmutable());

            $this->userService->save($user);
            $this->mailService->send(EmailType::FORGOT_PASSWORD,$user);
        }

        return $this->json(["message"=>"OK"],204);
    }
}
