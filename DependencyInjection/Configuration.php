<?php

namespace Afrihost\BaseCommandBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 * Use this link to see TreeBuilder options {@link http://symfony.com/doc/current/components/config/definition.html}
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
                ->arrayNode('php')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('memory_limit')->end()
                    ->end()
                ->end()
                ->arrayNode('locking')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('lock_file_folder')->defaultNull()->end()
                        ->booleanNode('enabled')->defaultValue(true)->end()
                    ->end()
                ->end()
                ->arrayNode('logger')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('handler_strategies')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('default')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->booleanNode('enabled')->defaultValue(true)->end()
                                        ->scalarNode('line_format')->defaultValue('%%datetime%% [%%level_name%%]: %%message%%')->end()
                                        ->scalarNode('file_extension')
                                            ->defaultValue('.log.txt')
                                            ->cannotBeEmpty()
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('console_stream')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->booleanNode('enabled')->defaultValue(true)->end()
                                        ->scalarNode('line_format')->defaultValue('%%datetime%% [%%level_name%%]: %%message%%')->end()
                                    ->end()
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
