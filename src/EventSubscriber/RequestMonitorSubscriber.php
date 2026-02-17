<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Logs every HTTP request after the response is sent (no impact on response time).
 * Output: route, method, URI, status code, duration, client IP.
 */
class RequestMonitorSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire(service: 'monolog.logger.request')]
        private LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => 'onKernelTerminate',
        ];
    }

    public function onKernelTerminate(TerminateEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        $route = $request->attributes->get('_route', 'unknown');

        // Skip internal/profiler routes
        if (str_starts_with($route, '_')) {
            return;
        }

        $durationMs = 0;
        $requestTime = $request->server->get('REQUEST_TIME_FLOAT');
        if ($requestTime) {
            $durationMs = round((microtime(true) - (float) $requestTime) * 1000);
        }

        $statusCode = $response->getStatusCode();
        $context = [
            'route'       => $route,
            'method'      => $request->getMethod(),
            'uri'         => $request->getPathInfo(),
            'status_code' => $statusCode,
            'duration_ms' => $durationMs,
            'client_ip'   => $request->getClientIp(),
        ];

        match (true) {
            $statusCode >= 500 => $this->logger->error('Request completed', $context),
            $statusCode >= 400 => $this->logger->warning('Request completed', $context),
            default            => $this->logger->info('Request completed', $context),
        };
    }
}
