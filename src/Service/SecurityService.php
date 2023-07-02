<?php

namespace App\Service;

use App\Entity\User;
use App\Service\MailService;
use App\Service\UserService;
use DateInterval;
use DateTimeImmutable;
use EmailType;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityService
{
    private TranslatorInterface $translator;
    private UserService $userService;
    private UserPasswordHasherInterface $passwordHasher;
    private TokenGeneratorInterface $tokenGenerator;
    private MailService $mailService;

    public function __construct(TranslatorInterface $translator, UserService $userService, UserPasswordHasherInterface $passwordHasher, TokenGeneratorInterface $tokenGenerator, MailService $mailService)
    {
        $this->translator = $translator;
        $this->userService = $userService;
        $this->passwordHasher = $passwordHasher;
        $this->tokenGenerator = $tokenGenerator;
        $this->mailService = $mailService;
    }

    /**
     * Comparaison de la version envoyé par l'utilisateur et la version minimum requis
     *
     * @param [type] $actualVersion Version à vérifier
     * @param [type] $currentVersion Version minimum requis
     * @return void
     */
    public function checkVersion($actualVersion, $currentVersion)
    {
        if (!$actualVersion) {
            throw new BadRequestException($this->translator->trans("version is empty"));
        }

        $isUpdated = version_compare($currentVersion, $actualVersion, "<=");

        if (!$isUpdated) {
            throw new PreconditionFailedHttpException($this->translator->trans("update required"), null, 426);
        }
    }

    /**
     * Inscription d'un utilisateur
     *
     * @param [type] $email email de l'utilisateur
     * @param [type] $password Mot de passe de l'utilisateur
     * @param [type] $firstname Prénom de l'utilisateur
     * @param [type] $lastname Nom de famille de l'utilisateur
     * @return void
     */
    public function register($email, $password, $firstname, $lastname)
    {
        $alreadyRegisted = $this->userService->findOneBy(["email" => $email]);

        if ($alreadyRegisted) {
            throw new BadRequestHttpException($this->translator->trans("Email already used"));
        }

        $user = new User();
        $user->setEmail($email);
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $password
        );
        $user->setPassword($hashedPassword);
        $user->setFirstname($firstname);
        $user->setLastname($lastname);

        $this->sendVerificationEmail($user);

        $this->userService->save($user, true);
    }

    /**
     * Envoie d'une mail d vérification d'email
     *
     * @param User $user Utilisateur à qui envoyer le mail
     * @return void
     */
    public function sendVerificationEmail(User $user){
        $user->setVerificationToken($this->tokenGenerator->generateToken());

        $this->mailService->send(EmailType::REGISTRATION, $user);
    }

    /**
     * Réinitialisation du mot de passe
     *
     * @param User $user Utilisateur qui veut réinitialiser son mot de passe
     * @param [type] $newPassword Nouveau mot de passe
     * @param [type] $passwordResetLimit Delais limite de validation du lien de réinitialisation
     * @return void
     */
    public function resetPassword(User $user, $newPassword, $passwordResetLimit)
    {
        //VERIFICATION PASSWORD_RESET_LIMIT
        $limitPasswordReset = $user->getResetAt()->add(new DateInterval("PT" . $passwordResetLimit . "M"));
        if ($limitPasswordReset < new DateTimeImmutable()) {
            throw new BadRequestHttpException($this->translator->trans("The time limit for changing the password has elapsed"));
        }

        $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);
        $user->setPasswordToken(null);
        $user->setResetAt(null);
        $this->userService->save($user);
    }

    /**
     * Demande de réinitialisation de mot de passe
     *
     * @param User $user Utilisateur qui veut réinitialiser son mot de passe
     * @return void
     */
    public function askResetpassword(User $user){
        $user->setPasswordToken($this->tokenGenerator->generateToken());
        $user->setResetAt(new DateTimeImmutable());

        $this->userService->save($user);
        $this->mailService->send(EmailType::FORGOT_PASSWORD, $user);
    }
}
