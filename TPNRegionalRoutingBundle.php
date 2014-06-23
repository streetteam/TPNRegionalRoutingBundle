<?php

namespace TPN\RegionalRoutingBundle;

use TPN\RegionalRoutingBundle\DependencyInjection\TPNRegionalRoutingExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * TPNRegionalRoutingBundle.
 *
 * @author Wojciech Kulikowski <wojciech.kulikowski@gmail.com>
 */
class TPNRegionalRoutingBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {

    }

    public function getContainerExtension()
    {
        return new TPNRegionalRoutingExtension();
    }
}
