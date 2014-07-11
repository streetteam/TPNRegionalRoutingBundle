<?php

namespace TPN\RegionalRoutingBundle\Tests\Router;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use TPN\RegionalRoutingBundle\Router\RouteRegionalizer;
use Mockery as M;

/**
 * Class RouteRegionalizerTest
 * @author Wojciech Kulikowski <kulikowski.wojciech@gmail.com>
 */
class RouteRegionalizerTest extends \PHPUnit_Framework_TestCase
{
    public function testRegionalize()
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add('foo', new Route('/foo'));
        $excluder = M::mock('TPN\RegionalRoutingBundle\Router\RegionRouteExcluder');
        $excluder->shouldReceive('isExcluded')->andReturn(false);

        $regionalizer = new RouteRegionalizer(array('gb', 'us', 'rest'), $excluder);
        $newRouteCollection = $regionalizer->createRegionalizedRoutes($routeCollection);

        $this->assertCount(4,$newRouteCollection);

    }

    public function testDoNotRegionalizeUnderscored()
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add('_foo', new Route('/_foo'));

        $excluder = M::mock('TPN\RegionalRoutingBundle\Router\RegionRouteExcluder');
        $excluder->shouldReceive('isExcluded')->andReturn(true);

        $regionalizer = new RouteRegionalizer(array('gb', 'us', 'rest'), $excluder);
        $newRouteCollection = $regionalizer->createRegionalizedRoutes($routeCollection);

        $this->assertCount(1,$newRouteCollection);

    }

    public function testDoNotRegionalizeTwice()
    {
        $routeCollection = new RouteCollection();
        $route =new Route('/foo');
        $route->setOption('isRegionalized', true);
        $routeCollection->add('foo', $route);
        $excluder = M::mock('TPN\RegionalRoutingBundle\Router\RegionRouteExcluder');
        $excluder->shouldReceive('isExcluded')->andReturn(false);

        $regionalizer = new RouteRegionalizer(array('gb', 'us', 'rest'), $excluder);
        $newRouteCollection = $regionalizer->createRegionalizedRoutes($routeCollection);

        $this->assertCount(1,$newRouteCollection);
    }

    public function testDoNotRegionalizeExcluded()
    {
        $routeCollection = new RouteCollection();
        $route =new Route('/foo');
        $routeCollection->add('foo', $route);
        $excluder = M::mock('TPN\RegionalRoutingBundle\Router\RegionRouteExcluder');
        $excluder->shouldReceive('isExcluded')->andReturn(true);

        $regionalizer = new RouteRegionalizer(array('gb', 'us', 'rest'), $excluder);
        $newRouteCollection = $regionalizer->createRegionalizedRoutes($routeCollection);

        $this->assertCount(1,$newRouteCollection);
    }
}
