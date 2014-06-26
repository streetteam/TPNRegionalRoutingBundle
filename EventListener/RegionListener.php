<?php

namespace TPN\RegionalRoutingBundle\EventListener;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use TPN\RegionalRoutingBundle\Exception\RegionNotFoundException;
use TPN\RegionalRoutingBundle\Factory\RegionCookieFactory;
use TPN\RegionalRoutingBundle\Router\RegionalRouter;
use TPN\RegionalRoutingBundle\Router\RegionResolver;

/**
 * Class RegionListener
 * @author Wojciech Kulikowski <kulikowski.wojciech@gmail.com>
 */
class RegionListener implements EventSubscriberInterface
{
    private $router;
    private $resolver;
    private $regionCookieFactory;

    /**
     * @param RegionResolver      $resolver
     * @param RegionCookieFactory $regionCookieFactory
     * @param RegionalRouter      $router
     * @param $regionChooseRoute
     */
    public function __construct(RegionResolver $resolver, RegionalRouter $router, RegionCookieFactory $regionCookieFactory, $regionChooseRoute)
    {
        $this->resolver = $resolver;
        $this->router = $router;
        $this->regionCookieFactory = $regionCookieFactory;
        $this->regionChooseRoute = $regionChooseRoute;

    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        try {
            $region = $this->resolver->resolveRegion();
        } catch (RegionNotFoundException $e) {
            if ($request->get('_route') != $this->regionChooseRoute) {
                $landingUrl = $this->router->generate($this->regionChooseRoute);
                $event->setResponse(new RedirectResponse($landingUrl));
            }

            return;
        }
        $this->router->getContext()->setParameter('_region', $region);

        $request->cookies->set('_region', $region);
        $request->attributes->set('_region', $region);
        $request->getSession()->set('_region', $region);

        $routeRegion = $this->resolver->getRouteRegion();
        if ($routeRegion != $region) {
            $route = $request->get('_route');
            if ('_' == $route[0]) {
                return;
            }
            $routeParams = array_merge($request->get('_route_params'), array('_region'=> $region));
            $url = $this->router->generate($route, $routeParams);
            $event->setResponse(new RedirectResponse($url));
        }
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        $cookie = $this->regionCookieFactory->create($request->attributes->get('_region'));
        $response->headers->setCookie($cookie);
    }

    public static function getSubscribedEvents()
    {
        return array(
            // must be registered after the Router to have access to the _region
            KernelEvents::REQUEST => array(array('onKernelRequest', 31)),
            KernelEvents::RESPONSE => 'onKernelResponse',
        );
    }
}
