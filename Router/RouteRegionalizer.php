<?php

namespace TPN\RegionalRoutingBundle\Router;

use Symfony\Component\Routing\RouteCollection;

class RouteRegionalizer
{
    private $regions;

    public function __construct(array $regions)
    {
        $this->regions = $regions;
    }

    public function createRegionalizedRoutes(RouteCollection $collection)
    {
        $regionalCollection = new RouteCollection();

        foreach ($collection->getResources() as $resource) {
            $regionalCollection->addResource($resource);
        }

        foreach ($collection->all() as $name => $route) {

            if ('_' === $name[0] || $route->getOption('isRegionalized')) {
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
                $regionalCollection->add($region.RegionalRouter::PREFIX.$name, $regionalRoute);
            }
        }

        return $regionalCollection;
    }
}
