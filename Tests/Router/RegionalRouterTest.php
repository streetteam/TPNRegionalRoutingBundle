<?php

namespace TPN\RegionalRoutingBundle\Tests\Router;

use Symfony\Component\Routing\Route;
use TPN\RegionalRoutingBundle\Router\RegionalRouter;
use Symfony\Component\Routing\RouteCollection;
use Mockery as M;

/**
 * Class RegionalRouterTest
 * @author Wojciech Kulikowski <kulikowski.wojciech@gmail.com>
 */
class RegionalRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGenerateNonExistantRoute()
    {
        $router = $this->getRouter();
        $router->generate('foo');
    }

    public function testGenerateRegionalizedWithBadRegion()
    {
        $router = $this->getRouter();
        $this->assertEquals('/bar',$router->generate('bar',array('_region' => 'de')));

    }

    public function testGenerate()
    {
        $router = $this->getRouter();
        $this->assertEquals('/bar',$router->generate('bar'));
        $this->assertEquals('/gb/bar',$router->generate('bar',array('_region' => 'gb')));
    }

    public function testGenerateWithContext()
    {
        $router = $this->getRouter();
        $router->getContext()->setParameter('_region', 'gb');

        $this->assertEquals('/gb/bar',$router->generate('bar'));
    }

    /**
     * @return RegionalRouter
     */
    private function getRouter()
    {
       $container = M::mock('Symfony\Component\DependencyInjection\Container');
       $routeCollection = new RouteCollection();
       $routeCollection->add('bar', new Route('/bar'));
       $routeCollection->add('gb--RR--bar', new Route('/gb/bar'));

       $container->shouldReceive('get->load')->andReturn($routeCollection);
       $routeRegionalizer = M::mock('TPN\RegionalRoutingBundle\Router\RouteRegionalizer');
       $routeRegionalizer->shouldReceive('createRegionalizedRoutes')->andReturn($routeCollection);

       $regionalRouter = new RegionalRouter($container, 'foo');
       $regionalRouter->setRouteRegionalizer($routeRegionalizer);

       return $regionalRouter;
    }
}
