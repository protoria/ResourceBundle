<?php

namespace Igdr\Bundle\ResourceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html//cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('resources');

        $this->addResourcesSection($rootNode);

        return $treeBuilder;
    }

    /**
     * Adds `resources` section.
     *
     * @param ArrayNodeDefinition $node
     */
    private function addResourcesSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('resources')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('controller')
                                ->children()
                                    ->scalarNode('id')
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->arrayNode('index')
                                        ->children()
                                            ->scalarNode('template')
                                                ->cannotBeEmpty()
                                            ->end()
                                            ->scalarNode('redirect')
                                                ->cannotBeEmpty()
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('edit')
                                        ->children()
                                            ->scalarNode('template')
                                                ->cannotBeEmpty()
                                            ->end()
                                            ->scalarNode('redirect')
                                                ->cannotBeEmpty()
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('delete')
                                        ->children()
                                            ->scalarNode('redirect')
                                                ->cannotBeEmpty()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('manager')
                                ->children()
                                    ->scalarNode('class')
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('entity')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('repository')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->arrayNode('order')
                                        ->requiresAtLeastOneElement()
                                        ->useAttributeAsKey('name')
                                        ->prototype('scalar')
                                        ->end()
                                    ->end()
                                    ->arrayNode('where')
                                        ->requiresAtLeastOneElement()
                                        ->useAttributeAsKey('name')
                                        ->prototype('array')
                                            ->requiresAtLeastOneElement()
                                            ->useAttributeAsKey('name')
                                            ->prototype('scalar')
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                ->end()
            ->end();
    }
}
