<?php

namespace TPN\RegionalRoutingBundle\Tests\Router;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use TPN\RegionalRoutingBundle\Router\RouteRegionalizer;

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

        $regionalizer = new RouteRegionalizer(array('gb', 'us', 'rest'));
        $regionalizedRouteCollectio = $regionalizer->createRegionalizedRoutes($routeCollection);

        $this->assertCount(4,$regionalizedRouteCollectio);

    }

    public function testDoNotRegionalizeRegionalize()
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add('_foo', new Route('/_foo'));

        $regionalizer = new RouteRegionalizer(array('gb', 'us', 'rest'));
        $regionalizedRouteCollectio = $regionalizer->createRegionalizedRoutes($routeCollection);

        $this->assertCount(1,$regionalizedRouteCollectio);

    }

    public function testDoNotRegionalizeTwice()
    {
        $routeCollection = new RouteCollection();
        $route =new Route('/foo');
        $route->setOption('isRegionalized', true);
        $routeCollection->add('foo', $route);

        $regionalizer = new RouteRegionalizer(array('gb', 'us', 'rest'));
        $regionalizedRouteCollectio = $regionalizer->createRegionalizedRoutes($routeCollection);

        $this->assertCount(1,$regionalizedRouteCollectio);
    }
}
