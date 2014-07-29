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
     * @var array
     */
    private $validRegions;
    /**
     * @var string
     */
    private $fallbackRegion;
    /**
     * @param GeoipManager $geoIp
     * @param Request      $request
     * @param array        $validRegions
     */
    public function __construct(GeoipManager $geoIp, Request $request, array $validRegions, $fallbackRegion)
    {
        $this->geoIp = $geoIp;
        $this->request = $request;
        $this->validRegions = $validRegions;
        $this->fallbackRegion = $fallbackRegion;
    }

    /**
     * @return string                  region
     * @throws RegionNotFoundException
     */
    public function resolveRegion()
    {
        $sessionRegion = $this->getSessionRegion();
        if ($this->isRegionValid($sessionRegion)) {
            return $sessionRegion;
        }

        $cookieRegion = $this->getCookieRegion();
        if ($this->isRegionValid($cookieRegion)) {
            return $cookieRegion;
        }

        $routeRegion = $this->getRouteRegion();
        if ($this->isRegionValid($routeRegion)) {
            return $routeRegion;
        }

        $geoIpRegion = $this->getGeoIpRegion();
        if ($this->isRegionValid($geoIpRegion)) {
            return $geoIpRegion;
        }

        $fallbackRegion = $this->getFallbackRegion();
        if ($this->isRegionValid($fallbackRegion)) {
            return $fallbackRegion;
        }

        throw new RegionNotFoundException("Region not found");
    }

    private function isRegionValid($region)
    {
        if (in_array($region, $this->validRegions)) {
            return true;
        }

        return false;
    }

    public function getFallbackRegion()
    {
        return $this->fallbackRegion;
    }

    /**
     * @return string|null region
     */
    public function getRouteRegion()
    {
        return $this->request->attributes->get('_region');
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

        return $lookup ? strtolower($lookup->getCountryCode()) : null;
    }

}
