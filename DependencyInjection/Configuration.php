<?php

namespace TPN\RegionalRoutingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @author Wojciech Kulikowski <kulikowski.wojciech@gmail.com>
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('tpn_regional_router');
        $rootNode->children()
            ->arrayNode('regions')
                ->isRequired()
                ->requiresAtLeastOneElement()
                ->prototype('scalar')->end()
            ->end()
            ->arrayNode('cookie')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('lifetime')
                        ->defaultValue(3600 * 24 * 365 * 10)
                    ->end()
                ->end()
            ->end()
            ->scalarNode('choose_region_route')
                ->isRequired()
            ->end()
            ->scalarNode('fallback')
                ->defaultValue(null)
            ->end()
        ->end();

        return $treeBuilder;
    }
}
