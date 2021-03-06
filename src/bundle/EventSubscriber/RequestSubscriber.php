<?php

namespace Gie\GatewayBundle\EventSubscriber;

use Gie\Gateway\Core\Cache\CacheManager;
use Gie\Gateway\Core\Request\RequestHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestSubscriber implements EventSubscriberInterface
{
    private $routes;

    public function __construct(array $routes = [])
    {
        $this->routes = $routes;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 0]],
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if ($request->attributes->get('_controller') !== 'gie_gateway.controller') {
            return;
        }

        $target = $request->attributes->get('target');
        $forwardHeaders = \explode(',', $request->headers->get('x-gateway-forward', ''));

        $params = [
            'method' => $request->getMethod(),
            'query' => $request->query->all(),
            'headers' => array_filter(
                $request->headers->all(),
                function ($header) use ($forwardHeaders) {
                    return in_array($header, $forwardHeaders);
                }),
            'ttl' => $request->headers->get('x-gateway-ttl', CacheManager::DEFAULT_TTL),
            'aggregator' => $request->headers->get('x-gateway-aggregator', 'array'),
        ];

        if (isset($this->routes[$target])) {
            $params = \array_replace_recursive($this->routes[$target], $params);

            $params['ttl'] = $params['ttl'] !== null
                ? $params['ttl']
                : $this->routes[$target]['ttl'];

            $params['aggregator'] = $params['aggregator'] !== null
                ? RequestHelper::getAggregator($params['aggregator'])
                : RequestHelper::getAggregator($this->routes[$target]['aggregator']);
        } else {
            $params['target'] = $target;
            $params['aggregator'] = RequestHelper::getAggregator($params['aggregator']);
        }

        foreach ($params as $key => $value) {
            $request->attributes->set($key, $value);
        }

    }
}