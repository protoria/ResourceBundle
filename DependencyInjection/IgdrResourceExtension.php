<?php
namespace Igdr\Bundle\ResourceBundle\DependencyInjection;

use Igdr\Bundle\ResourceBundle\IgdrResource;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
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
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        //load configuration
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

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
            if (is_file($file = dirname($reflection->getFilename()).'/Resources/config/resources.yml')) {
                $bundleConfig = Yaml::parse(file_get_contents(realpath($file)));
                if (is_array($bundleConfig)) {
                    $configuredResources = array_replace_recursive($configuredResources, $bundleConfig);
                }
            }
        }

        // validate menu configurations
        $configuration = new ResourceConfiguration();
        $configuredResources = $this->processConfiguration($configuration, array('resources' => $configuredResources));

        if (!empty($configuredResources)) {
            $this->createRouting($configuredResources['resources'], $container);
            $this->createManagers($configuredResources['resources'], $container);
            $this->createForms($configuredResources['resources'], $container);
        }
    }

    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    private function createRouting(array $configs, ContainerBuilder $container)
    {
        $definition = new Definition('Igdr\Bundle\ResourceBundle\Routing\ResourceLoader');
        $definition->addTag('routing.loader');
        $definition->setArguments(array($configs, $container->getParameter('igdr_resource.config.defaults')));

        $container->setDefinition('resource.routing.loader', $definition);
    }

    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    private function createManagers(array $configs, ContainerBuilder $container)
    {
        foreach ($configs as $name => $config) {
            $arr = explode('.', $name);
            $manager = $config['manager'];

            $definition = new DefinitionDecorator('igdr_manager.manager.standard');
            if (isset($manager['class'])) {
                $definition->setClass($manager['class']);
            }
            $definition->setArguments(array($manager['entity'], $manager['repository'], $manager['where'], $manager['order'], $manager['cache']));
            $definition->addTag(IgdrResource::SERVICE_TAG);
            $definition->setShared(false);
            $container->setDefinition($arr[0].'.manager.'.$arr[1], $definition);

            //register in parameters
            foreach ($manager as $key => $value) {
                $container->setParameter(sprintf('igdr_resource.config.defaults.%s.manager.%s', $name, $key), $value);
            }
        }
    }

    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    private function createForms(array $configs, ContainerBuilder $container)
    {
        foreach ($configs as $name => $config) {
            if (empty($config['controller']['id'])) {
                continue;
            }

            $arr = explode('.', $name);
            //form
            if (!empty($config['controller']['form'])) {
                $id = $arr[0].'.form.'.$arr[1];
                $class = $config['controller']['form'];
                if ($container->hasDefinition($id) === false && class_exists($class)) {
                    $definition = new Definition($class);
                    $definition->addTag('form.type', array('alias' => str_replace('.', '_', $name)));

                    $reflection = new \ReflectionClass($class);
                    if ($reflection->implementsInterface('\Symfony\Component\DependencyInjection\ContainerAwareInterface')) {
                        $definition->addMethodCall('setContainer', [new Reference('service_container')]);
                    }

                    $container->setDefinition($id, $definition);
                }
            }

            //grid
            if (!empty($config['controller']['grid'])) {
                $id = $arr[0].'.grid.'.$arr[1];
                $class = $config['controller']['grid'];
                if (class_exists($class)) {
                    $definition = new Definition($class);

                    $reflection = new \ReflectionClass($class);
                    if ($reflection->implementsInterface('\Symfony\Component\DependencyInjection\ContainerAwareInterface')) {
                        $definition->addMethodCall('setContainer', [new Reference('service_container')]);
                    }

                    $container->setDefinition($id, $definition);
                }
            }
        }
    }
}
