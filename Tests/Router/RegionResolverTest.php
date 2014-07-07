<?php

namespace TPN\RegionalRoutingBundle\Tests\Router;

use Mockery as M;
use TPN\RegionalRoutingBundle\Router\RegionResolver;

/**
 * Class RegionResolverTest
 * @author Wojciech Kulikowski <kulikowski.wojciech@gmail.com>
 */
class RegionResolverTest extends \PHPUnit_Framework_TestCase
{
    private $geoIpRecord;
    private $geoIpManager;
    private $request;

    public function setUp()
    {
        $this->geoIpRecord = M::mock('Maxmind\lib\GeoIpRecord');

        $this->geoIpManager = M::mock('Maxmind\Bundle\GeoipBundle\Service\GeoipManager');
        $this->geoIpManager->shouldReceive('lookup')->andReturn($this->geoIpRecord);

        $this->request = M::mock('Symfony\Component\HttpFoundation\Request');
    }

    /**
     * When no region found.
     * @expectedException TPN\RegionalRoutingBundle\Exception\RegionNotFoundException
     */
    public function testResolveRegionGeoIp()
    {

        $session = M::mock('Symfony\Component\HttpFoundation\Session\Session');
        $session->shouldReceive('get')->with('_region')->andReturn(null);
        $this->request->shouldReceive('getSession')->andReturn($session);

        $this->request->cookies =  M::mock('Symfony\Component\HttpFoundation\ParameterBag');
        $this->request->cookies->shouldReceive('get')->with('_region')->andReturn(null);

        $this->request->shouldReceive('get')->with('_route')->andReturn('route_name');

        $this->geoIpRecord->shouldReceive('getCountryCode')->andReturn(false);
        $this->request->shouldReceive('getClientIp')->andReturn('127.0.0.1');

        $regionResolver = new RegionResolver($this->geoIpManager, $this->request, array('pl'), null);
        $this->assertEquals('pl', $regionResolver->resolveRegion());
    }

    /**
     * Fallback region.
     */
    public function testResolveRegionFallback()
    {

        $session = M::mock('Symfony\Component\HttpFoundation\Session\Session');
        $session->shouldReceive('get')->with('_region')->andReturn(null);
        $this->request->shouldReceive('getSession')->andReturn($session);

        $this->request->cookies =  M::mock('Symfony\Component\HttpFoundation\ParameterBag');
        $this->request->cookies->shouldReceive('get')->with('_region')->andReturn(null);

        $this->request->shouldReceive('get')->with('_route')->andReturn('route_name');

        $this->geoIpRecord->shouldReceive('getCountryCode')->andReturn(false);
        $this->request->shouldReceive('getClientIp')->andReturn('127.0.0.1');

        $regionResolver = new RegionResolver($this->geoIpManager, $this->request, array('pl'), 'pl');
        $this->assertEquals('pl', $regionResolver->resolveRegion());
    }

    /**
     * When region found.
     */
    public function testResolveRegion()
    {

        $session = M::mock('Symfony\Component\HttpFoundation\Session\Session');
        $session->shouldReceive('get')->with('_region')->andReturn(null);
        $this->request->shouldReceive('getSession')->andReturn($session);

        $this->request->cookies =  M::mock('Symfony\Component\HttpFoundation\ParameterBag');
        $this->request->cookies->shouldReceive('get')->with('_region')->andReturn(null);

        $this->request->shouldReceive('get')->with('_route')->andReturn('route_name');

        $this->geoIpRecord->shouldReceive('getCountryCode')->andReturn('pl');
        $this->request->shouldReceive('getClientIp')->andReturn('127.0.0.1');

        $regionResolver = new RegionResolver($this->geoIpManager, $this->request, array('pl'), null);
        $this->assertEquals('pl', $regionResolver->resolveRegion());
    }

    public function testGetSessionRegion()
    {

        $session = M::mock('Symfony\Component\HttpFoundation\Session\Session');
        $session->shouldReceive('get')->with('_region')->andReturn('pl');
        $this->request->shouldReceive('getSession')->andReturn($session);

        $regionResolver = new RegionResolver($this->geoIpManager, $this->request, array('pl'), null);

        $this->assertEquals('pl', $regionResolver->getSessionRegion());
    }

    public function testGetCookieRegion()
    {
        $this->request->cookies =  M::mock('Symfony\Component\HttpFoundation\ParameterBag');
        $this->request->cookies->shouldReceive('get')->with('_region')->andReturn('pl');

        $regionResolver = new RegionResolver($this->geoIpManager, $this->request, array('pl'), null);

        $this->assertEquals('pl', $regionResolver->getCookieRegion());
    }

    public function testGetRouteRegion()
    {
        $this->request->shouldReceive('get')->with('_route')->andReturn('pl--RR--route_name');

        $regionResolver = new RegionResolver($this->geoIpManager, $this->request, array('pl'), null);

        $this->assertEquals('pl', $regionResolver->getRouteRegion());
    }

    public function testGetGeoIpRegion()
    {
        $this->geoIpRecord->shouldReceive('getCountryCode')->andReturn('pl');
        $this->request->shouldReceive('getClientIp')->andReturn('127.0.0.1');

        $regionResolver = new RegionResolver($this->geoIpManager, $this->request, array('pl'), null);

        $this->assertEquals('pl', $regionResolver->getGeoIpRegion());

    }
    public function tearDown()
    {
        $this->geoIpRecord = null;
        $this->geoIpManager = null;

    }
}
