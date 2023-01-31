<?php

namespace App\EventSubscriber;

use App\Entity\GlobalInformations;
use GlobalInformationsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestSubscriber implements EventSubscriberInterface
{
    private GlobalInformationsService $globalInformationsService;

    public function __construct(GlobalInformationsService $globalInformationsService)
    {
        $this->globalInformationsService = $globalInformationsService;
    }
    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return [
            KernelEvents::REQUEST => [
                ['addRequest', 10],
            ],
        ];
    }

    public function addRequest(RequestEvent $event)
    {
        if (str_starts_with($event->getRequest()->getPathInfo(), "/api")) {
            /** @var GlobalInformations */
            $globalInformations = $this->globalInformationsService->findOneBy(["name" => "ALL_REQUEST"]);
            $globalInformations->increment();

            $this->globalInformationsService->save($globalInformations);
        }
    }
}
