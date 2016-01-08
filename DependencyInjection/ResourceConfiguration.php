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
class ResourceConfiguration implements ConfigurationInterface
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
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('id')
                                        ->defaultValue('resource.controller.abstract')
                                    ->end()
                                    ->scalarNode('route')
                                    ->end()
                                    ->scalarNode('form')
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('grid')
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->arrayNode('redirect')
                                        ->children()
                                            ->scalarNode('route')
                                                ->cannotBeEmpty()
                                            ->end()
                                            ->arrayNode('parameters')
                                                ->treatNullLike(array())
                                                ->defaultValue(array())
                                                ->requiresAtLeastOneElement()
                                                ->useAttributeAsKey('name')
                                                ->prototype('scalar')
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('security')
                                        ->prototype('scalar')
                                        ->end()
                                    ->end()
                                    ->arrayNode('index')
                                        ->children()
                                            ->scalarNode('template')
                                                ->cannotBeEmpty()
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('edit')
                                        ->children()
                                            ->scalarNode('template')
                                                ->cannotBeEmpty()
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('delete')
                                        ->children()
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
                                    ->scalarNode('cache')
                                        ->defaultFalse()
                                    ->end()
                                    ->arrayNode('order')
                                        ->treatNullLike(array())
                                        ->defaultValue(array())
                                        ->requiresAtLeastOneElement()
                                        ->useAttributeAsKey('name')
                                        ->prototype('scalar')
                                        ->end()
                                    ->end()
                                    ->arrayNode('where')
                                        ->treatNullLike(array())
                                        ->defaultValue(array())
                                        ->requiresAtLeastOneElement()
                                        ->useAttributeAsKey('name')
                                        ->prototype('array')
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
