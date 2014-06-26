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
        $cookieFactory = new RegionCookieFactory();
        $cookie = $cookieFactory->create('pl');
        $this->assertGreaterThanOrEqual(time()+3600 * 24 * 365 * 10, $cookie->getExpiresTime());
        $this->assertEquals('_region', $cookie->getName());
        $this->assertEquals('pl', $cookie->getValue());
    }
}
