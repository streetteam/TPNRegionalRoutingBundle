<?php

namespace TPN\RegionalRoutingBundle\Router;

use Maxmind\Bundle\GeoipBundle\Service\GeoipManager;
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
     * @var string
     */
    private $webCrawlerRegion;

    /**
     * @param GeoipManager $geoIp
     * @param Request      $request
     * @param array        $validRegions
     */
    public function __construct(GeoipManager $geoIp, Request $request, $options)
    {
        $this->geoIp = $geoIp;
        $this->request = $request;

        $this->fallbackRegion = isset($options['fallbackRegion']) ? $options['fallbackRegion'] : null;
        $this->webCrawlerRegion = isset($options['webCrawlerRegion']) ? $options['webCrawlerRegion'] : null;
        $this->userAgents = isset($options['userAgents']) ? $options['userAgents'] : array();
        $this->validRegions = isset($options['validRegions']) ? $options['validRegions'] : array();

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

        $webCrawlerRegion = $this->getWebCrawlerRegion();
        if ($this->isRegionValid($webCrawlerRegion)) {
            return $webCrawlerRegion;
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
        return strstr($this->request->attributes->get('_route'), RegionalRouter::ROUTE_PREFIX, true);
    }

    /**
     * @return string|null region
     */
    public function getWebCrawlerRegion()
    {
        $clientUserAgent = $this->request->headers->get('User-Agent');
        foreach ($this->userAgents as $userAgent) {
            if (strstr(strtolower($clientUserAgent),strtolower($userAgent))) {
                return $this->webCrawlerRegion;
            }
        }

        return null;
    }

    /**
     * @return string|null region
     */
    public function getSessionRegion()
    {
        if (!$this->request->getSession()) {
            return null;
        }

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
