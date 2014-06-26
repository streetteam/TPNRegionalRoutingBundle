<?php

namespace TPN\RegionalRoutingBundle\Router;

use Maxmind\Bundle\GeoipBundle\Service\GeoipManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use TPN\RegionalRoutingBundle\Exception\RegionNotFoundException;

/**
 * Class RegionResolver
 * @author Wojciech Kulikowski <kulikowski.wojciech@gmail.com>
 */
class RegionResolver
{
    /**
     * @var GeoipManager
     */
    private $geoIp;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param GeoipManager $geoIp
     * @param Request      $request
     */
    public function __construct(GeoipManager $geoIp, Request $request)
    {
        $this->geoIp = $geoIp;
        $this->request = $request;
    }

    /**
     * @return string                  region
     * @throws RegionNotFoundException
     */
    public function resolveRegion()
    {

        $sessionRegion = $this->getSessionRegion();
        if (!empty($sessionRegion)) {
            return $sessionRegion;
        }

        $cookieRegion = $this->getCookieRegion();
        if (!empty($cookieRegion)) {
            return $cookieRegion;
        }

        $routeRegion = $this->getRouteRegion();
        if (!empty($routeRegion)) {
            return $routeRegion;
        }

        $geoIpRegion = $this->getGeoIpRegion();
        if (!empty($geoIpRegion)) {
            return $geoIpRegion;
        }

        throw new RegionNotFoundException("Region not found");
    }

    /**
     * @return string|null region
     */
    public function getRouteRegion()
    {
        return strstr($this->request->get('_route'), RegionalRouter::ROUTE_PREFIX, true);
    }

    /**
     * @return string|null region
     */
    public function getSessionRegion()
    {
        return $this->request->getSession()->get('_region');
    }

    /**
     * @return string|null region
     */
    public function getCookieRegion()
    {
        return $this->request->cookies->get('_region');
    }

    /**
     * @return mixed region
     */
    public function getGeoIpRegion()
    {
        $lookup = $this->geoIp->lookup($this->request->getClientIp());

        return $lookup ? $lookup->getCountryCode() : null;
    }

}
