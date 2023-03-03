<?php

namespace App\EventListener;

use App\Entity\Exception;
use App\Service\ExceptionService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{

    private ExceptionService $exceptionService;

    public function __construct(ExceptionService $exceptionService)
    {
        $this->exceptionService=$exceptionService;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $response = new Response();

        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace(["content-type"=>"application/json"]);
            $message = sprintf(json_encode(['message'=>$exception->getMessage(),'code'=>$exception->getStatusCode()]));
            $exception= (new Exception())
            ->setCode($exception->getStatusCode())
            ->setMessage($exception->getMessage());

            $this->exceptionService->save($exception);
        } else {
            $message = sprintf(json_encode(['message'=>$exception->getMessage(),'code'=>$exception->getCode()]));
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $response->setContent($message);
        $event->setResponse($response);
    }
}