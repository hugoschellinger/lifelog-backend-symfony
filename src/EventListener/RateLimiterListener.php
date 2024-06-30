<?php
// src/EventListener/ExceptionListener.php
namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class RateLimiterListener
{
    public function __construct(
        private RateLimiterFactory $apiLimiter,
        private RateLimiterFactory $loginCheckLimiter,
        private TranslatorInterface $translator,
        private ParameterBagInterface $bag
        )
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // if($this->bag->get('app.app_env') == "dev" || $this->bag->get('app.app_env') == "test"){
        //     return;
        // }

        // create a limiter based on a unique identifier of the client
        // (e.g. the client's IP address, a username/email, an API key, etc.)
        $limiter = $this->apiLimiter->create($event->getRequest()->getClientIp());

        // the argument of consume() is the number of tokens to consume
        // and returns an object of type Limit
        if (false === $limiter->consume(1)->isAccepted()) {
            throw new TooManyRequestsHttpException(null, $this->translator->trans('Too many requests. Please try again later.'));
        }
    }

    /**
 * @param AuthenticationFailureEvent $event
 */
public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event)
{
        // if($this->bag->get('app.app_env') == "dev" || $this->bag->get('app.app_env') == "test"){
        //     return;
        // }

        // create a limiter based on a unique identifier of the client
        // (e.g. the client's IP address, a username/email, an API key, etc.)
        $limiter = $this->loginCheckLimiter->create($event->getRequest()->getClientIp());

        // the argument of consume() is the number of tokens to consume
        // and returns an object of type Limit
        if (false === $limiter->consume(1)->isAccepted()) {
            throw new TooManyRequestsHttpException(null, $this->translator->trans('Too many requests. Please try again later.'));
        }
}
}