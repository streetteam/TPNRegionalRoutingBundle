<?php

namespace TPN\RegionalRoutingBundle\Router;

use Symfony\Component\Routing\Route;

class RegionRouteExcluder
{
    /**
     * @param  Route $route
     * @param $routeName
     * @return bool
     */
    public function isExcluded(Route $route, $routeName)
    {
        if ("_" == $routeName[0]) {
            return true;
        }

        if ($route->getOption('noRegionalization') === true) {
            return true;
        }

        return false;
    }
}
