<?php

namespace TPN\RegionalRoutingBundle\Router;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Regional Router
 *
 * @author Wojciech Kulikowski <kulikowski.wojciech@gmail.com>
 */
class RegionalRouter extends Router
{
    const PREFIX  = 'RR';

    private $routeRegionalizer;

    public function setRouteRegionalizer(RouteRegionalizer $routeRegionalizer)
    {
        $this->routeRegionalizer = $routeRegionalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollection()
    {
        $collection = parent::getRouteCollection();

        $this->collection = $this->routeRegionalizer->createRegionalizedRoutes($collection);

        return $this->collection;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {

        $region = $this->context->getParameter('_region');
        $name = $this->removePrefixFromRoute($name);
        if (isset($parameters['_region'])) {
            $region = $parameters['_region'];
            unset($parameters['_region']);
        }

        try {
            return parent::generate($region.self::PREFIX.$name, $parameters, $referenceType);
        } catch (RouteNotFoundException $ex) {
            return parent::generate($name, $parameters, $referenceType);
        }
    }

    private function removePrefixFromRoute($name)
    {
        $regionalRoute = strstr($name, RegionalRouter::PREFIX);
        if ($regionalRoute) {
            return str_replace(RegionalRouter::PREFIX, '', $regionalRoute);
        }

        return $name;
    }

}
