<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\FireBaseService;
use App\Service\MailService;
use App\Service\UserService;
use DateInterval;
use DateTimeImmutable;
use EmailType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    private UserService $userService;
    private MailService $mailService;
    private TranslatorInterface $translator;
    private TokenGeneratorInterface $tokenGenerator;

    public function __construct(UserService $userService, MailService $mailService, TranslatorInterface $translator, TokenGeneratorInterface $tokenGenerator)
    {
        $this->userService = $userService;
        $this->mailService = $mailService;
        $this->translator = $translator;
        $this->tokenGenerator = $tokenGenerator;
    }

    #[Route('/test', name: 'test', methods: ["GET"])]
    public function test(UserService $userService, FireBaseService $FireBaseService): Response
    {
        /** @var User */
        $user = $userService->findOneBy(["id" => $this->getUser()]);
        $FireBaseService->sendNotification($user, "heyy", "heyyyyyyyy");

        return $this->json("OK", 200);
    }

    #[Route('/version', name: 'check_version', methods: ["POST"])]
    public function checkVersion(Request $request): Response
    {
        $actualVersion = $request->toArray()["version"] ?? null;
        $currentVersion = $this->getParameter("app.mobile_version");

        if (!$actualVersion) {
            throw new BadRequestException($this->translator->trans("version is empty"));
        }

        $isUpdated = version_compare($currentVersion, $actualVersion, "<=");

        if (!$isUpdated) {
            throw new PreconditionFailedHttpException($this->translator->trans("update required"), null, 426);
        }

        return $this->json(["message", "OK"], 200);
    }

    #[Route('/whoami', name: 'whoami', methods: ["GET"])]
    public function whoami(NormalizerInterface $normalizer): Response
    {
        /** @var User */
        $user = $this->userService->findOneBy(["email" => $this->getUser()->getUserIdentifier()]);
        return $this->json([...$normalizer->normalize($user,"json",["groups" => ["User:read"]]),"isVerified" => $user->getVerificationToken() ? false: true]);
    }

    #[Route('/register', name: 'register', methods: ["POST"])]
    public function registration(Request $request, UserPasswordHasherInterface $passwordHasher)
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
        $user->setVerificationToken($this->tokenGenerator->generateToken());

        $this->mailService->send(EmailType::REGISTRATION, $user);

        $this->userService->save($user, true);

        return $this->json($user, 201, [], ["groups" => ["User:read"]]);
    }

    #[Route('/resetPassword', name: 'reset_password', methods: ["POST", "GET"])]
    public function resetPassword(Request $request, UserPasswordHasherInterface $hasher): Response
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

            //VERIFICATION PASSWORD_RESET_LIMIT
            $limitPasswordReset = $user->getResetAt()->add(new DateInterval("PT" . $this->getParameter("app.password_reset_limit") . "M"));
            if ($limitPasswordReset < new DateTimeImmutable()) {
                throw new BadRequestHttpException($this->translator->trans("The time limit for changing the password has elapsed"));
            }

            $hashedPassword = $hasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
            $user->setPasswordToken(null);
            $user->setResetAt(null);
            $this->userService->save($user);

            return $this->render('security/resetPassword.html.twig');
        } else {

            $email = $request->toArray()["email"] ?? null;

            if (!$email) {
                throw new BadRequestHttpException($this->translator->trans("Email is empty"));
            }

            $user = $this->userService->findOneBy(["email" => $email]);

            if ($user) {
                $user->setPasswordToken($this->tokenGenerator->generateToken());
                $user->setResetAt(new DateTimeImmutable());

                $this->userService->save($user);
                $this->mailService->send(EmailType::FORGOT_PASSWORD, $user);
            }

            return $this->json(["message" => "OK"], 204);
        }
    }
}
