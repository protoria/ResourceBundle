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
        $builder = new TreeBuilder();
        $rootNode = $builder->root('igdr_resource', 'array');

        $this->addResourcesSection($rootNode);

        return $builder;
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
                ->arrayNode('namespace')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('form')
                            ->cannotBeEmpty()
                            ->defaultValue('App\Bundle\{Bundle}Bundle\Form\Type\Admin\{Entity}Type')
                        ->end()
                        ->scalarNode('grid')
                            ->cannotBeEmpty()
                            ->defaultValue('App\Bundle\{Bundle}Bundle\Grid\Type\Admin\{Entity}Type')
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('base_url')
                    ->defaultValue('/admin')
                ->end()
                ->scalarNode('route_prefix')
                    ->defaultValue('admin')
                ->end()
                ->arrayNode('controller')
                    ->children()
                        ->arrayNode('security')
                            ->prototype('scalar')
                            ->end()
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
            ->end();
    }
}
