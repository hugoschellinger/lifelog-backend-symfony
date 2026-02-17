<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use App\Service\SecurityService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    private UserService $userService;
    private TranslatorInterface $translator;
    private SecurityService $securityService;
    private NormalizerInterface $normalizer;
    private JWTTokenManagerInterface $jwtManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        UserService $userService,
        TranslatorInterface $translator,
        SecurityService $securityService,
        NormalizerInterface $normalizer,
        JWTTokenManagerInterface $jwtManager,
        UserPasswordHasherInterface $passwordHasher
    )
    {
        $this->userService = $userService;
        $this->translator = $translator;
        $this->securityService = $securityService;
        $this->normalizer = $normalizer;
        $this->jwtManager = $jwtManager;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/test', name: 'test', methods: ["POST"])]
    public function test(UserService $userService): Response
    {
        /** @var User */
        $user=$userService->findOneBy(["id"=>$this->getUser()]);


        return $this->json("OK", 200);
    }

    #[Route('/login', name: 'api_login', methods: ["POST"])]
    public function login(Request $request): Response
    {
        $data = $request->toArray();
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            throw new BadRequestHttpException($this->translator->trans('Email or password missing'));
        }

        /** @var User|null $user */
        $user = $this->userService->findOneBy(['email' => $email]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
            return $this->json(['message' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        $token = $this->jwtManager->create($user);

        return $this->json([
            'user' => $user->getUserIdentifier(),
            'token' => $token,
        ]);
    }

    #[Route('/logout', name: 'api_logout')]
    public function logout(): Response
    {
        return $this->json([
            'message' => "OK"
        ]);
    }

    #[Route('/checkEmail', name: 'check_email', methods: ["POST"])]
    public function checkEmail(Request $request): Response
    {
        $email = $request->toArray()["email"] ?? null;

        if(!$email){
            throw new BadRequestHttpException($this->translator->trans("Email is empty"));
        }

        /** @var User */
        $user = $this->userService->findOneBy(["email" => $email]);

        if($user){
            throw new BadRequestException($this->translator->trans("Email already used"));
        }

        return $this->json("OK", 200);
    }

    #[Route('/version', name: 'check_version', methods: ["POST"])]
    public function checkVersion(Request $request): Response
    {
        $actualVersion = $request->toArray()["version"] ?? null;
        $currentVersion = $this->getParameter("app.mobile_version");

        $this->securityService->checkVersion($actualVersion,$currentVersion);

        return $this->json(["message", "OK"], 200);
    }

    #[Route('/whoami', name: 'whoami', methods: ["GET"])]
    public function whoami(): Response
    {
        $user = $this->getUser();
        return $this->json($this->normalizer->normalize($user,"json",["groups" => ["User:read"]]));
    }

    #[Route('/register', name: 'register', methods: ["POST"])]
    public function registration(Request $request)
    {
        $email = $request->toArray()["email"];
        $password = $request->toArray()["password"];
        $firstname = $request->toArray()["firstname"];
        $lastname = $request->toArray()["lastname"];

        $user = $this->securityService->register($email, $password, $firstname, $lastname);

        return $this->json($user, 201, [], ["groups" => ["User:read"]]);
    }

    #[Route('/resetPassword', name: 'reset_password', methods: ["POST", "GET"])]
    public function resetPassword(Request $request): Response
    {
        $token = $request->toArray()["token"] ?? null;

        if ($token) {

            /** @var User */
            $user = $this->userService->findOneBy(["passwordToken" => $token]);

            if (!$user) {
                throw new BadRequestHttpException($this->translator->trans("invalid token"));
            }

            //RETURN PASSWORD FORM
            if ($request->getMethod() == "GET") return $this->render('security/resetPassword.html.twig');

            $newPassword = $request->toArray()["newPassword"] ?? null;
            $repeatedPassword = $request->toArray()["repeatedPassword"] ?? null;

            if($repeatedPassword !== $newPassword){
                throw new BadRequestHttpException($this->translator->trans("Passwords are different"));
            }

            if (!$newPassword) {
                throw new BadRequestHttpException($this->translator->trans("New password is empty"));
            }

            $this->securityService->resetPassword($user, $newPassword, $this->getParameter("app.password_reset_limit"));

            return $this->render('security/resetPassword.html.twig');
        } else {

            $email = $request->toArray()["email"] ?? null;

            if (!$email) {
                throw new BadRequestHttpException($this->translator->trans("Email is empty"));
            }

            $user = $this->userService->findOneBy(["email" => $email]);

            if ($user) {
                $this->securityService->askResetpassword($user);
            }

            return $this->json(["message" => "OK"], 200);
        }
    }
}
