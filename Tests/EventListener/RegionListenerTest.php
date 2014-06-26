<?php

namespace TPN\RegionalRoutingBundle\Tests\EventListener;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\KernelEvents;
use TPN\RegionalRoutingBundle\EventListener\RegionListener;
use Mockery as M;
use TPN\RegionalRoutingBundle\Factory\RegionCookieFactory;

/**
 * Class RegionListenerTest
 * @author Wojciech Kulikowski <kulikowski.wojciech@gmail.com>
 */
class RegionListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testRequestAttributes()
    {
        $requestContext = M::mock('Symfony\Component\Routing\RequestContext');
        $requestContext->shouldReceive('setParameter')->with('_region','de');

        $router = M::mock('TPN\RegionalRoutingBundle\Router\RegionalRouter');
        $router->shouldReceive('getContext')->andReturn($requestContext);

        $session = M::mock('Symfony\Component\HttpFoundation\Session\Session');
        $session->shouldReceive('set')->with('_region','de');

        $request= M::mock('Symfony\Component\HttpFoundation\Request');
        $request->attributes = M::mock('Symfony\Component\HttpFoundation\ParameterBag')->shouldReceive('set')->with('de');
        $request->cookies = M::mock('Symfony\Component\HttpFoundation\ParameterBag')->shouldReceive('set')->with('de');
        $request->shouldReceive('getSession')->andReturn($session);

        $responseEvent = M::mock('Symfony\Component\HttpKernel\Event\GetResponseEvent');
        $responseEvent->shouldReceive('getRequest')->andReturn($request);

        $regionResolver = M::mock('TPN\RegionalRoutingBundle\Router\RegionResolver');
        $regionResolver->shouldReceive('resolveRegion')->andReturn('de');
        $regionResolver->shouldReceive('getRouteRegion')->andReturn('de');

        $regionCookieFactory = new RegionCookieFactory();

        $regionListener = new RegionListener($regionResolver, $router, $regionCookieFactory, 'test_route');
        $regionListener->onKernelRequest($responseEvent);
    }

    public function testSubscribedEvents()
    {
        $this->assertEquals(
            array(
                KernelEvents::REQUEST => array(array('onKernelRequest', 31)),
                KernelEvents::RESPONSE => 'onKernelResponse',
            ),

            RegionListener::getSubscribedEvents());
    }

}
