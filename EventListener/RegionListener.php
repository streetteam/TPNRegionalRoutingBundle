<?php
/**
 * Created by PhpStorm.
 * User: qlik
 * Date: 23.06.14
 * Time: 15:27
 */

namespace TPN\RegionalRoutingBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RequestContextAwareInterface;

class RegionListener implements EventSubscriberInterface
{
    private $router;

    public function __construct(RequestContextAwareInterface $router = null)
    {
        $this->router = $router;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $region = null;

        if ($request->attributes->get('_region')) {
            $region = strtolower($request->attributes->get('_region'));
        }
        if ($request->cookies->get('regionSelected')) {
            $region = strtolower($request->cookies->get('regionSelected'));
        }

        if ($request->getSession()->get('countryCode')) {
            $region = strtolower($request->getSession()->get('countryCode'));
        }

        $this->router->getContext()->setParameter('_region', $region);

    }

    public static function getSubscribedEvents()
    {
        return array(
            // must be registered after the Router to have access to the _region
            KernelEvents::REQUEST => array(array('onKernelRequest', 31)),
        );
    }
}
