<?php
/**
 * Created by PhpStorm.
 * User: qlik
 * Date: 23.06.14
 * Time: 15:27
 */

namespace TPN\RegionalRoutingBundle\EventListener;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use TPN\AdminBundle\Sonata\Region;
use TPN\RegionalRoutingBundle\Exception\RegionNotFoundException;
use TPN\RegionalRoutingBundle\Router\RegionalRouter;
use TPN\RegionalRoutingBundle\Router\RegionResolver;

class RegionListener implements EventSubscriberInterface
{
    private $router;
    private $resolver;

    public function __construct(RegionResolver $resolver, RegionalRouter $router, $regionChooseRoute)
    {
        $this->resolver = $resolver;
        $this->router = $router;
        $this->regionChooseRoute = 'tpn_promoter_landing_page_show';

    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        try {
            $region = $this->resolver->resolveRegion();
        } catch (RegionNotFoundException $e) {
            if ($request->get('_route') != $this->regionChooseRoute) {
                $landingUrl = $this->router->generate($this->regionChooseRoute, array(
                    'brand' => 'tpn',
                ));
                $event->setResponse(new RedirectResponse($landingUrl));
            }

            return;
        }
        $this->router->getContext()->setParameter('_region', $region);
        $request->cookies->set('_region', $region);
        $request->attributes->set('_region', $region);

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

        $cookie = new Cookie(
            '_region',
            $request->attributes->get('_region'),
            time() + 3600 * 24 * 365 * 10,
            '/'
        );

        //if ($session->get('setRegionSelectedCookie')) {
            $response->headers->setCookie($cookie);
        //}
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
