<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\MailService;
use EmailType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class SecurityController extends AbstractController
{
    private UserRepository $userRepository;
    private MailService $mailService;

    public function __construct(UserRepository $userRepository, MailService $mailService)
    {
        $this->userRepository = $userRepository;
        $this->mailService = $mailService;
    }

    #[Route('/register', name: 'register',methods:["POST"])]
    public function registration(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $email = $request->toArray()["email"] ?? null;
        $password = $request->toArray()["password"] ?? null;
        $firstname = $request->toArray()["firstname"] ?? null;
        $lastname = $request->toArray()["lastname"] ?? null;

        if (!$email) {
            throw new BadRequestHttpException("L'email n'est pas renseigné");
        }
        if (!$password) {
            throw new BadRequestHttpException("Le mot de passe n'est pas renseigné");
        }
        if (!$firstname) {
            throw new BadRequestHttpException("Le prénom n'est pas renseigné");
        }
        if (!$lastname) {
            throw new BadRequestHttpException("Le nom n'est pas renseigné");
        }

        $alreadyRegisted = $this->userRepository->findOneBy(["email" => $email]);

        if ($alreadyRegisted) {
            throw new BadRequestHttpException("Cette email est déjà utilisé");
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

        $this->userRepository->save($user, true);



        return $this->json($user, 201);
    }
}
