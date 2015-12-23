<?php

namespace Afrihost\BaseCommandBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('afrihost_base_command');

        /** @var $rootNode ArrayNodeDefinition */

        // @formatter:off
        $rootNode
            ->children()
                ->arrayNode('logger')
                    ->children()
                        ->arrayNode('handler_strategies')->children()
                            ->arrayNode('default')
                                ->children()
                                    ->scalarNode('file_extention')->defaultValue('.log.txt')->end()
                                ->end()
                            ->end()
                            ->arrayNode('console_stream')
                                ->children()
                                    ->booleanNode('enabled')->defaultValue(true)->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
        // @formatter:on

        return $treeBuilder;
    }
}
