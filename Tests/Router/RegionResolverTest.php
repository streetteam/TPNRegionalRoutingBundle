<?php

namespace TPN\RegionalRoutingBundle\Tests\Router;

use Maxmind\Bundle\GeoipBundle\Service\GeoipManager;
use Maxmind\lib\GeoIpRecord;
use Mockery as M;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use TPN\RegionalRoutingBundle\Exception\RegionNotFoundException;
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

        $flashBag = new FlashBag();
        $this->request->shouldReceive('getSession->getFlashBag')->andReturn($flashBag);

        $this->request->cookies =  M::mock('Symfony\Component\HttpFoundation\ParameterBag');
        $this->request->cookies->shouldReceive('get')->with('_region')->andReturn(null);

        $this->request->shouldReceive('get')->with('_route')->andReturn('route_name');

        $this->geoIpRecord->shouldReceive('getCountryCode')->andReturn(false);
        $this->request->shouldReceive('getClientIp')->andReturn('127.0.0.1');

        $regionResolver = new RegionResolver($this->geoIpManager, $this->request);
        var_dump($regionResolver->resolveRegion());
    }

    public function testGetFlashBagRegion()
    {

        $flashBag = new FlashBag();
        $flashBag->set('_region','pl');
        $this->request->shouldReceive('getSession->getFlashBag')->andReturn($flashBag);

        $regionResolver = new RegionResolver($this->geoIpManager, $this->request);

        $this->assertEquals('pl', $regionResolver->getFlashBagRegion());
    }

    public function testGetCookieRegion()
    {
        $this->request->cookies =  M::mock('Symfony\Component\HttpFoundation\ParameterBag');
        $this->request->cookies->shouldReceive('get')->with('_region')->andReturn('pl');

        $regionResolver = new RegionResolver($this->geoIpManager, $this->request);

        $this->assertEquals('pl', $regionResolver->getCookieRegion());
    }

    public function testGetRouteRegion()
    {
        $this->request->shouldReceive('get')->with('_route')->andReturn('pl--RR--route_name');

        $regionResolver = new RegionResolver($this->geoIpManager, $this->request);

        $this->assertEquals('pl', $regionResolver->getRouteRegion());
    }

    public function testGetGeoIpRegion()
    {
        $this->geoIpRecord->shouldReceive('getCountryCode')->andReturn('pl');
        $this->request->shouldReceive('getClientIp')->andReturn('127.0.0.1');

        $regionResolver = new RegionResolver($this->geoIpManager, $this->request);

        $this->assertEquals('pl', $regionResolver->getGeoIpRegion());

    }
    public function tearDown()
    {
        $this->geoIpRecord = null;
        $this->geoIpManager = null;

    }
}
