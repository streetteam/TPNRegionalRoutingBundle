<?php

namespace TPN\RegionalRoutingBundle\Tests\EventListener;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
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
    public function testOnKernelRequest()
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

        $regionCookieFactory = M::mock('TPN\RegionalRoutingBundle\Factory\RegionCookieFactory');

        $regionListener = new RegionListener($regionResolver, $router, $regionCookieFactory, 'test_route');
        $regionListener->onKernelRequest($responseEvent);
    }

    public function testOnKernelResponse()
    {
        $router = M::mock('TPN\RegionalRoutingBundle\Router\RegionalRouter');
        $cookie = M::mock('Symfony\Component\HttpFoundation\Cookie');

        $response = M::mock('Symfony\Component\HttpFoundation\Response');
        $response->headers = M::mock('Symfony\Component\HttpFoundation\ResponseHeaderBag');
        $response->headers->shouldReceive('setCookie')->with($cookie);

        $request= M::mock('Symfony\Component\HttpFoundation\Request');
        $request->attributes = new ParameterBag(array('_route'=>'de'));

        $responseEvent = M::mock('Symfony\Component\HttpKernel\Event\GetResponseEvent');
        $responseEvent->shouldReceive('getRequest')->andReturn($request);

        $regionResolver = M::mock('TPN\RegionalRoutingBundle\Router\RegionResolver');

        $regionCookieFactory = M::mock('TPN\RegionalRoutingBundle\Factory\RegionCookieFactory');
        $regionCookieFactory->shouldReceive('create')->andReturn($cookie);
        $filterResponseEvenet = M::mock('Symfony\Component\HttpKernel\Event\FilterResponseEvent');
        $filterResponseEvenet->shouldReceive('getRequest')->andReturn($request);
        $filterResponseEvenet->shouldReceive('getResponse')->andReturn($response);

        $regionListener = new RegionListener($regionResolver, $router, $regionCookieFactory, 'test_route');
        $regionListener->onKernelResponse($filterResponseEvenet);
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
