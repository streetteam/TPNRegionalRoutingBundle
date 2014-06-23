<?php
/**
 * Created by PhpStorm.
 * User: qlik
 * Date: 23.06.14
 * Time: 16:58
 */

namespace TPN\RegionalRoutingBundle\Tests\EventListener;

use Symfony\Component\HttpFoundation\ParameterBag;
use TPN\RegionalRoutingBundle\EventListener\RegionListener;
use Mockery as M;

class RegionListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testRequestAttributes()
    {
        $requestContext = M::mock('Symfony\Component\Routing\RequestContext');
        $requestContext->shouldReceive('setParameter')->with('_region','de');

        $router = M::mock('TPN\RegionalRoutingBundle\Router\RegionalRouter');
        $router->shouldReceive('getContext')->andReturn($requestContext);

        $request= M::mock('Symfony\Component\HttpFoundation\Request');
        $request->shouldReceive('getSession')->andReturn(new ParameterBag(array('countryCode', 'DE')));
        $request->attributes = new ParameterBag(array('_region' => 'de'));
        $request->cookies = new ParameterBag(array('regionSelected' => 'de'));

        $responseEvent = M::mock('Symfony\Component\HttpKernel\Event\GetResponseEvent');
        $responseEvent->shouldReceive('getRequest')->andReturn($request);

        $regionListener = new RegionListener($router);
        $regionListener->onKernelRequest($responseEvent);
    }

}
