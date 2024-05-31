<?php

namespace App\EventSubscriber;

use App\Entity\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use App\Service\ExceptionService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private ExceptionService $exceptionService,
        private ParameterBagInterface $bag
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

        $exception = $event->getThrowable();
        $response = new Response();

        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace(["content-type" => "application/json"]);
            $message = sprintf(json_encode(['message' => $exception->getMessage(), 'code' => $exception->getStatusCode()]));
            if ($exception->getStatusCode() !== 404) {

                $exception = (new Exception())
                    ->setCode($exception->getStatusCode())
                    ->setMessage($exception->getMessage());

                $this->exceptionService->save($exception);
            }
        } else {
            $message = sprintf(json_encode(['message' => $exception->getMessage(), 'code' => $exception->getCode()]));
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $response->setContent($message);
        $event->setResponse($response);

        if($this->bag->get('app.app_env') == "dev" || $this->bag->get('app.app_env') == "test"){
            dump($exception->getMessage());
        }else{
            $this->logger->error($exception->getMessage());
        }
    }
}