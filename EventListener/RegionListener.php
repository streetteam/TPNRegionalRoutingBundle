<?php

namespace TPN\RegionalRoutingBundle\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use TPN\RegionalRoutingBundle\Exception\RegionNotFoundException;
use TPN\RegionalRoutingBundle\Factory\RegionCookieFactory;
use TPN\RegionalRoutingBundle\Router\RegionalRouter;
use TPN\RegionalRoutingBundle\Router\RegionResolver;
use TPN\RegionalRoutingBundle\Router\RegionRouteExcluder;

/**
 * Class RegionListener
 * @author Wojciech Kulikowski <kulikowski.wojciech@gmail.com>
 */
class RegionListener implements EventSubscriberInterface
{
    private $router;
    private $resolver;
    private $regionCookieFactory;
    private $regionChooseRoute;
    private $excluder;

    /**
     * @param RegionResolver      $resolver
     * @param RegionCookieFactory $regionCookieFactory
     * @param RegionalRouter      $router
     * @param $regionChooseRoute
     */
    public function __construct(RegionResolver $resolver, RegionalRouter $router, RegionCookieFactory $regionCookieFactory, RegionRouteExcluder $excluder, $regionChooseRoute)
    {
        $this->resolver = $resolver;
        $this->router = $router;
        $this->regionCookieFactory = $regionCookieFactory;
        $this->regionChooseRoute = $regionChooseRoute;
        $this->excluder = $excluder;

    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $routeName = $request->get('_route');
        $route = $this->router->getRouteCollection()->get($routeName);
        if (null == $route || $this->excluder->isExcluded($route, $routeName)) {
            return;
        }

        try {
            $region = $this->resolver->resolveRegion();
        } catch (RegionNotFoundException $e) {
            if ($request->get('_route') != $this->regionChooseRoute) {
                $landingUrl = $this->router->generate($this->regionChooseRoute, $request->query->all());

                $event->setResponse(new RedirectResponse($landingUrl));
            }

            return;
        }
        $this->router->getContext()->setParameter('_region', $region);

        $request->getSession()->set('_region', $region);

        $routeRegion = $this->resolver->getRouteRegion();

        if ($routeRegion != $region) {
            $routeParams = array_merge($request->get('_route_params'), array('_region'=> $region), $request->query->all());
            $url = $this->router->generate($routeName, $routeParams);

            $event->setResponse(new RedirectResponse($url));
        }
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $cookieRegion = $this->resolver->getCookieRegion();
        if (!$event->getRequest()->getSession()) {
            return;
        }
        $region = $event->getRequest()->getSession()->get('_region');

        if (empty($region) & !empty($cookieRegion)) {
            return;
        }

        $response = $event->getResponse();

        $cookie = $this->regionCookieFactory->create($region);
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
