<?php

namespace TPN\RegionalRoutingBundle\Router;

use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouteRegionalizer
 * @author Wojciech Kulikowski <kulikowski.wojciech@gmail.com>
 */
class RouteRegionalizer
{
    private $regions;
    private $excluder;

    /**
     * @param array $regions
     */
    public function __construct(array $regions, RegionRouteExcluder $excluder)
    {
        $this->regions = $regions;
        $this->excluder = $excluder;
    }

    /**
     * @param  RouteCollection $collection
     * @return RouteCollection
     */
    public function createRegionalizedRoutes(RouteCollection $collection)
    {
        $regionalCollection = new RouteCollection();

        foreach ($collection->getResources() as $resource) {
            $regionalCollection->addResource($resource);
        }

        foreach ($collection->all() as $name => $route) {

            if ($this->excluder->isExcluded($route, $name) || $route->getOption('isRegionalized')) {
                $regionalCollection->add($name, $route);
                continue;
            }

            $regionalRoute = clone $route;
            $regionalCollection->add($name, $regionalRoute);

            foreach ($this->regions as $region) {
                $regionalRoute = clone $route;
                $regionalRoute->setOption('isRegionalized', true);
                $regionalRoute->setOption('_region', $region);
                $regionalRoute->setPattern($region.$regionalRoute->getPattern());
                $regionalCollection->add($region.RegionalRouter::ROUTE_PREFIX.$name, $regionalRoute);
            }
        }

        return $regionalCollection;
    }
}
