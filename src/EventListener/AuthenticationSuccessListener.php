<?php
namespace App\EventListener;

use App\Entity\User;
use App\Service\UserService;
use DateTimeImmutable;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthenticationSuccessListener
{

    private UserService $userService;
    private TranslatorInterface $translator;

    public function __construct(UserService $userService, TranslatorInterface $translator)
    {
        $this->userService = $userService;
        $this->translator = $translator;
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
    );

    $event->setData($data);
}
}
