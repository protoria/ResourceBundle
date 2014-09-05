<?php
namespace Igdr\Bundle\ResourceBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class IgdrResourceExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        //load configuration
        $configuration = $this->getConfiguration($configs, $container);
        $config        = $this->processConfiguration($configuration, $configs);

        $container->setParameter('igdr_resource.config.defaults', $config);

        //load resource configuration
        $this->loadResourcesConfiguration($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadResourcesConfiguration(ContainerBuilder $container)
    {
        $configuredResources = array();

        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            if (is_file($file = dirname($reflection->getFilename()) . '/Resources/config/resources.yml')) {
                $bundleConfig = Yaml::parse(realpath($file));
                if (is_array($bundleConfig)) {
                    $configuredResources = array_replace_recursive($configuredResources, $bundleConfig);
                }
            }
        }

        // validate menu configurations
        $configuration       = new ResourceConfiguration();
        $configuredResources = $this->processConfiguration($configuration, array('resources' => $configuredResources));

        if (!empty($configuredResources)) {
            $this->createResourceRouting($configuredResources['resources'], $container);
            $this->createResourceServices($configuredResources['resources'], $container);
        }
    }

    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    private function createResourceRouting(array $configs, ContainerBuilder $container)
    {
        $definition = new Definition('Igdr\Bundle\ResourceBundle\Routing\ResourceLoader');
        $definition->addTag('routing.loader');
        $definition->setArguments(array($configs));

        $container->setDefinition('resource.routing.loader', $definition);
    }

    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    private function createResourceServices(array $configs, ContainerBuilder $container)
    {
        foreach ($configs as $name => $config) {
            $arr          = explode('.', $name);
            $bundleName   = ucfirst($arr[0]);
            $resourceName = ucfirst($arr[1]);

            //form
            $definition = new Definition(sprintf('App\Bundle\%sBundle\Form\Type\%sType', $bundleName, $resourceName));
            $definition->addTag('form.type', array('alias' => str_replace('.', '_', $name)));
            $container->setDefinition($arr[0] . '.form.' . $arr[1], $definition);

            //grid
            $definition = new Definition(sprintf('App\Bundle\%sBundle\Grid\Type\%sType', $bundleName, $resourceName));
            $container->setDefinition($arr[0] . '.grid.' . $arr[1], $definition);

            //manager
            if (isset($config['manager'])) {
                $manager = $config['manager'];

                $definition = new DefinitionDecorator('igdr_manager.manager.standard');
                if (isset($manager['class'])) {
                    $definition->setClass($manager['class']);
                }
                $definition->setArguments(array($manager['entity'], $manager['repository'], @$manager['where'], @$manager['order']));
                $definition->setScope('prototype');
                $container->setDefinition($arr[0] . '.manager.' . $arr[1], $definition);
            }
        }
    }
}
