<?php

namespace TPN\RegionalRoutingBundle\Factory;

use Symfony\Component\HttpFoundation\Cookie;

/**
 * Class RegionCookieFactory
 * @author Wojciech Kulikowski <kulikowski.wojciech@gmail.com>
 */
class RegionCookieFactory
{

    private $lifetime;

    public function __construct($lifetime)
    {
        $this->lifetime = $lifetime;
    }
    /**
     * @param $region
     * @return Cookie
     */
    public function create($region)
    {
        return new Cookie(
            '_region',
            $region,
            time() + $this->lifetime,
            '/'
        );
    }
}
