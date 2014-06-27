<?php

namespace TPN\RegionalRoutingBundle\Tests\Factory;

use TPN\RegionalRoutingBundle\Factory\RegionCookieFactory;

/**
 * Class RegionCookieFactoryTest
 * @author Wojciech Kulikowski <kulikowski.wojciech@gmail.com>
 */
class RegionCookieFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $lifetime = 3600;
        $cookieFactory = new RegionCookieFactory($lifetime);
        $cookie = $cookieFactory->create('pl');
        $this->assertGreaterThanOrEqual(time()+$lifetime, $cookie->getExpiresTime());
        $this->assertEquals('_region', $cookie->getName());
        $this->assertEquals('pl', $cookie->getValue());
    }
}
