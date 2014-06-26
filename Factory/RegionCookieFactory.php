<?php

namespace TPN\RegionalRoutingBundle\Factory;

use Symfony\Component\HttpFoundation\Cookie;

/**
 * Class RegionCookieFactory
 * @author Wojciech Kulikowski <kulikowski.wojciech@gmail.com>
 */
class RegionCookieFactory
{
    /**
     * @param $region
     * @return Cookie
     */
    public function create($region)
    {
        return new Cookie(
            '_region',
            $region,
            time() + 3600 * 24 * 365 * 10,
            '/'
        );
    }
}
