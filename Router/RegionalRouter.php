<?php

namespace TPN\RegionalRoutingBundle\Router;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Class RegionalRouter
 * @author Wojciech Kulikowski <kulikowski.wojciech@gmail.com>
 */
class RegionalRouter extends Router
{
    const ROUTE_PREFIX  = '--RR--';

    /**
     * @var RouteRegionalizer
     */
    private $routeRegionalizer;

    /**
     * @param  RouteRegionalizer $routeRegionalizer
     * @return RegionalRouter
     */
    public function setRouteRegionalizer(RouteRegionalizer $routeRegionalizer)
    {
        $this->routeRegionalizer = $routeRegionalizer;

        return $this;
    }

    public function getRouteCollection()
    {
        $collection = parent::getRouteCollection();

        $this->collection = $this->routeRegionalizer->createRegionalizedRoutes($collection);

        return $this->collection;
    }

    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {

        $region = $this->context->getParameter('_region');
        $name = $this->removePrefixFromRoute($name);
        if (isset($parameters['_region'])) {
            $region = $parameters['_region'];
            unset($parameters['_region']);
        }

        try {
            return parent::generate($region.static::ROUTE_PREFIX.$name, $parameters, $referenceType);
        } catch (RouteNotFoundException $ex) {
            return parent::generate($name, $parameters, $referenceType);
        }
    }

    /**
     * @param $name
     * @return string non-regionalized route
     */
    public function removePrefixFromRoute($name)
    {
        $regionalRoute = strstr($name, static::ROUTE_PREFIX);
        if ($regionalRoute) {
            return str_replace(static::ROUTE_PREFIX, '', $regionalRoute);
        }

        return $name;
    }

    /**
     * @return string|null region
     */
    public function getRegionFromRoute($route)
    {
        return strstr($route, static::ROUTE_PREFIX, true);
    }

    /**
     * {@inheritdoc}
     */
    public function match($pathinfo)
    {
        $match = $this->getMatcher()->match($pathinfo);
        $match['_region'] = $this->getRegionFromRoute($match['_route']);
        $match['_route'] = $this->removePrefixFromRoute($match['_route']);

        return $match;
    }

}
