<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\FireBaseService;
use App\Service\UserService;
use App\Service\SecurityService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    private UserService $userService;
    private TranslatorInterface $translator;
    private SecurityService $securityService;
    private NormalizerInterface $normalizer;

    public function __construct(UserService $userService, TranslatorInterface $translator, SecurityService $securityService, NormalizerInterface $normalizer)
    {
        $this->userService = $userService;
        $this->translator = $translator;
        $this->securityService = $securityService;
        $this->normalizer = $normalizer;
    }

    #[Route('/login', name: 'api_login', methods: ["POST"])]
    public function login(
        #[CurrentUser] ?User $user,
    ): Response
    {
        if(null == $user){
            return $this->json(["message" => "Invalid credentials"], Response::HTTP_UNAUTHORIZED);
        }

        $token = "abc";


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

    #[Route('/test', name: 'test', methods: ["GET"])]
    public function test(JWTTokenManagerInterface $JWTManager): Response
    {
        $user = $this->userService->find(1);

        return $this->json(['token' => $JWTManager->create($user)]);
    }

    #[Route('/checkEmail', name: 'check_email', methods: ["POST"])]
    public function checkEmail(Request $request): Response
    {
        $email = $request->toArray()["email"] ?? null;

        if(!$email){
            throw new BadRequestException($this->translator->trans("Email is empty"));
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
        /** @var User */
        $user = $this->userService->findOneBy(["email" => $this->getUser()->getUserIdentifier()]);
        return $this->json([...$this->normalizer->normalize($user,"json",["groups" => ["User:read"]]),"isVerified" => $user->getVerificationToken() ? false: true]);
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
        $token = $request->get("token") ?? null;

        if ($token) {

            /** @var User */
            $user = $this->userService->findOneBy(["passwordToken" => $token]);

            if (!$user) {
                throw new BadRequestHttpException($this->translator->trans("invalid token"));
            }

            //RETURN PASSWORD FORM
            if ($request->getMethod() == "GET") return $this->render('security/resetPassword.html.twig');

            $newPassword = $request->get("newPassword") ?? null;
            $repeatedPassword = $request->get("repeatedPassword") ?? null;

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

            return $this->json(["message" => "OK"], 204);
        }
    }

    #[Route('/connect/google', name: 'connect_with_google', methods: ["POST"])]
    public function connectWithGoogle(Request $request, JWTTokenManagerInterface $JWTManager): Response
    {
        $email = $request->toArray()["email"] ?? null;
        $googleIdToken = $request->toArray()["googleIdToken"] ?? null;
        $firstname = $request->toArray()["firstname"] ?? null;
        $lastname = $request->toArray()["lastname"] ?? null;

        $user = $this->userService->findOneBy(["email" => $email]);

        if($user){
            $user->setGoogleIdToken($googleIdToken);
            // $this->userService->save($alreadyUsed);

            return $this->json(["token" => $JWTManager->create($user), "data" => [...$this->normalizer->normalize($user,"json",["groups" => ["User:read"]]),"isVerified" => $user->getVerificationToken() ? false: true]]);
        }else{
            $user = $this->securityService->registerWithGoogle($email, $googleIdToken, $firstname, $lastname);
            return $this->json(["token" => $JWTManager->create($user), "data" => [...$this->normalizer->normalize($user,"json",["groups" => ["User:read"]]),"isVerified" => $user->getVerificationToken() ? false: true]], 201);
        }
    }
}
