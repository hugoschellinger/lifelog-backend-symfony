<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserService extends AbstractService
{

    private TranslatorInterface $translator;

    public function __construct(UserRepository $userRepository, TranslatorInterface $translator)
    {
        parent::__construct($userRepository);
        $this->translator=$translator;
    }

    public function verificationEmail($email, $code){
        if(!$code){
            throw new BadRequestHttpException("Token is empty");
        }
        if(!$email){
            throw new BadRequestHttpException("Email is empty");
        }

        /** @var User */
        $user = $this->findOneBy(["verificationToken" => $code, "email" => $email]);

        if(!$user){
            throw new BadRequestHttpException("code or email is wrong");
        }

        $user->setVerificationToken(null);

        $this->save($user);
    }
}