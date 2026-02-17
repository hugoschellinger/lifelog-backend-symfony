<?php
namespace App\EventListener;

use App\Entity\User;
use App\Service\UserService;
use DateTimeImmutable;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

class AuthenticationSuccessListener
{

    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        $user->setLastConnexion(new DateTimeImmutable());
        $this->userService->save($user,true);

        $data['data'] = array(
            'roles' => $user->getRoles(),
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'lastname' => $user->getLastname(),
            'firstname' => $user->getFirstname(),
            'isVerified' => $user->getIsVerified(),
        );

        $event->setData($data);
    }
}
