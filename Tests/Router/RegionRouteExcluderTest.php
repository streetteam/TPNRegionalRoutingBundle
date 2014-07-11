<?php

namespace TPN\RegionalRoutingBundle\Tests\Router;

use Mockery as M;
use TPN\RegionalRoutingBundle\Router\RegionRouteExcluder;

class RegionRouteExcluderTest extends \PHPUnit_Framework_TestCase
{
    public function testUnderscoreRouteName()
    {
        $excluder = new RegionRouteExcluder();
        $route = M::mock('Symfony\Component\Routing\Route');

        $this->assertTrue($excluder->isExcluded($route, '_test_route'));
    }

    public function testThereShouldBeNoRegionalizationForThisRoute()
    {
        $excluder = new RegionRouteExcluder();

        $route = M::mock('Symfony\Component\Routing\Route');
        $route->shouldReceive('getOption')->with('noRegionalization')->andReturn(true);
        $route->shouldReceive('getOption')->with('isRegionalized')->andReturn(false);

        $this->assertTrue($excluder->isExcluded($route, 'test_route'));
    }

    public function testNotExcludedRoute()
    {
        $excluder = new RegionRouteExcluder();

        $route = M::mock('Symfony\Component\Routing\Route');
        $route->shouldReceive('getOption')->with('noRegionalization')->andReturn(null);
        $route->shouldReceive('getOption')->with('isRegionalized')->andReturn(false);

        $this->assertFalse($excluder->isExcluded($route, 'test_route'));
    }

}
