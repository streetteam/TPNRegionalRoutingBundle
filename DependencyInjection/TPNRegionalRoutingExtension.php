<?php

namespace TPN\RegionalRoutingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class TPNRegionalRoutingExtension
 * @author Wojciech Kulikowski <kulikowski.wojciech@gmail.com>
 */
class TPNRegionalRoutingExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(array(__DIR__.'/../Resources/config')));
        $loader->load('services.xml');

        $container->setParameter('tpn_regional_routing.regions', $config['regions']);
        $container->setParameter('tpn_regional_routing.cookie.lifetime', $config['cookie']['lifetime']);
        $container->setParameter('tpn_regional_routing.choose_region_route', $config['choose_region_route']);
    }

    public function getAlias()
    {
        return 'tpn_regional_routing';
    }
}
