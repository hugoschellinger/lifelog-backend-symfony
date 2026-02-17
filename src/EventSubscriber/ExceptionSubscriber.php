<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private ParameterBagInterface $bag,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        $request = $event->getRequest();

        if ($throwable instanceof HttpExceptionInterface) {
            $statusCode = $throwable->getStatusCode();
            $responseData = [
                'message' => $throwable->getMessage(),
                'code' => $statusCode,
            ];
        } else {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            $responseData = [
                'message' => $throwable->getMessage(),
                'code' => $throwable->getCode(),
            ];
        }

        $response = new JsonResponse($responseData, $statusCode);
        $event->setResponse($response);

        $context = [
            'exception'  => $throwable,
            'route'      => $request->attributes->get('_route', 'unknown'),
            'method'     => $request->getMethod(),
            'uri'        => $request->getPathInfo(),
            'client_ip'  => $request->getClientIp(),
            'status_code' => $statusCode,
        ];

        if ($this->bag->get('app.app_env') === 'dev' || $this->bag->get('app.app_env') === 'test') {
            dump($throwable);
        } else {
            $this->logger->error($throwable->getMessage(), $context);
        }
    }
}