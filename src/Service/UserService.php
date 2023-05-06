<?php

namespace App\Service;

use App\Repository\UserRepository;
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

    public function verificationEmail($token){
        if(!$token){
            throw new BadRequestHttpException($this->translator->trans("Token is empty"));
        }

        /** @var User */
        $user = $this->findOneBy(["verificationToken" => $token]);

        if(!$user){
            throw new BadRequestHttpException($this->translator->trans("Link is wrong"));
        }

        $user->setVerificationToken(null);

        $this->save($user);
    }
}