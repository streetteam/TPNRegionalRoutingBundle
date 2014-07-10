<?php

namespace TPN\RegionalRoutingBundle\Tests\EventListener;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Route;
use TPN\RegionalRoutingBundle\EventListener\RegionListener;
use Mockery as M;

/**
 * Class RegionListenerTest
 * @author Wojciech Kulikowski <kulikowski.wojciech@gmail.com>
 */
class RegionListenerTest extends \PHPUnit_Framework_TestCase
{

    private $requestContext;
    private $router;
    private $session;
    private $request;
    private $response;
    private $getResponseEvent;
    private $filterResponseEvent;
    private $regionCookieFactory;

    public function setUp()
    {
        $this->requestContext = M::mock('Symfony\Component\Routing\RequestContext');

        $this->router = M::mock('TPN\RegionalRoutingBundle\Router\RegionalRouter');
        $this->router->shouldReceive('getContext')->andReturn($this->requestContext);
        $this->router->shouldReceive('getRouteCollection->get')->andReturn(new Route('/test_path'));

        $this->session = M::mock('Symfony\Component\HttpFoundation\Session\Session');

        $this->response = M::mock('Symfony\Component\HttpFoundation\Response');
        $this->response->headers = M::mock('Symfony\Component\HttpFoundation\ResponseHeaderBag');

        $this->request= M::mock('Symfony\Component\HttpFoundation\Request');
        $this->request->attributes = M::mock('Symfony\Component\HttpFoundation\ParameterBag');
        $this->request->cookies = M::mock('Symfony\Component\HttpFoundation\ParameterBag');
        $this->request->shouldReceive('getSession')->andReturn($this->session);
        $this->request->shouldReceive('get')->with('_route')->andReturn('test_route');
        $this->request->shouldReceive('get')->with('_route_params')->andReturn(array());

        $this->filterResponseEvent = M::mock('Symfony\Component\HttpKernel\Event\FilterResponseEvent');
        $this->filterResponseEvent->shouldReceive('getRequest')->andReturn($this->request);
        $this->filterResponseEvent->shouldReceive('getResponse')->andReturn($this->response);

        $this->getResponseEvent = M::mock('Symfony\Component\HttpKernel\Event\GetResponseEvent');
        $this->getResponseEvent->shouldReceive('getRequest')->andReturn($this->request);

        $this->regionResolver = M::mock('TPN\RegionalRoutingBundle\Router\RegionResolver');
        $this->regionCookieFactory = M::mock('TPN\RegionalRoutingBundle\Factory\RegionCookieFactory');
    }

    public function testOnKernelRequest()
    {
        $this->requestContext->shouldReceive('setParameter')->with('_region','de');
        $this->regionResolver->shouldReceive('resolveRegion')->andReturn('de');
        $this->regionResolver->shouldReceive('getRouteRegion')->andReturn('de');

        $this->session->shouldReceive('set')->with('_region', 'de');
        $this->request->attributes->shouldReceive('set')->with('_region', 'de');
        $this->request->cookies->shouldReceive('set')->with('_region', 'de');

        $excluder = M::mock('TPN\RegionalRoutingBundle\Router\RegionRouteExcluder');
        $excluder->shouldReceive('isExcluded')->andReturn(false);

        $regionListener = new RegionListener($this->regionResolver, $this->router, $this->regionCookieFactory,$excluder, 'test_route');
        $regionListener->onKernelRequest($this->getResponseEvent);
    }

    public function testEmptyRouteParamsOnKernelRequest()
    {
        $this->requestContext->shouldReceive('setParameter')->with('_region','pl');
        $this->regionResolver->shouldReceive('resolveRegion')->andReturn('pl');
        $this->regionResolver->shouldReceive('getRouteRegion')->andReturn('de');

        $this->session->shouldReceive('set')->with('_region', 'pl');
        $this->request->attributes->shouldReceive('set')->with('_region', 'pl');
        $this->request->cookies->shouldReceive('set')->with('_region', 'pl');
        $this->request->shouldReceive('get')->with('_route')->andReturn('test_route');
        $this->request->shouldReceive('get')->with('_route_params', array())->andReturn(array());
        $this->router->shouldReceive('generate')->andReturn ('http://localhost');
        $this->getResponseEvent->shouldReceive('setResponse');

        $excluder = M::mock('TPN\RegionalRoutingBundle\Router\RegionRouteExcluder');
        $excluder->shouldReceive('isExcluded')->andReturn(false);

        $regionListener = new RegionListener($this->regionResolver, $this->router, $this->regionCookieFactory, $excluder, 'test_route');
        $regionListener->onKernelRequest($this->getResponseEvent);
    }

    public function testOnKernelResponse()
    {
        $cookie = M::mock('Symfony\Component\HttpFoundation\Cookie');

        $excluder = M::mock('TPN\RegionalRoutingBundle\Router\RegionRouteExcluder');
        $excluder->shouldReceive('isExcluded')->andReturn(false);

        $this->response->headers->shouldReceive('setCookie')->with($cookie);
        $this->request->attributes = new ParameterBag(array('_route'=>'de'));
        $this->regionCookieFactory->shouldReceive('create')->andReturn($cookie);

        $regionListener = new RegionListener($this->regionResolver, $this->router, $this->regionCookieFactory, $excluder, 'test_route');
        $regionListener->onKernelResponse($this->filterResponseEvent);
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

    public function tearDown()
    {
        $this->requestContext = null;
        $this->router = null;
        $this->session = null;
        $this->request = null;
        $this->response = null;
        $this->getResponseEvent = null;
        $this->filterResponseEvent = null;
        $this->regionCookieFactory = null;
    }

}
