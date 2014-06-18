<?php

namespace TPN\RegionalRoutingBundle\Router;

use TPN\RegionalRoutingBundle\Exception\NotAcceptableLanguageException;

use TPN\RegionalRoutingBundle\Router\RegionalLoader;
use TPN\RegionalRoutingBundle\Router\LocaleResolverInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Regional Router
 *
 * @author Wojciech Kulikowski <kulikowski.wojciech@gmail.com>
 */
class RegionalRouter extends Router
{

}
