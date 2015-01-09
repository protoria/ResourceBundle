<?php
namespace Igdr\Bundle\ResourceBundle\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Catalog router builder
 */
class ResourceLoader implements LoaderInterface
{
    /**
     * @var bool
     */
    private $loaded = false;

    /**
     * @var array
     */
    private $resources = array();

    /**
     * @var array
     */
    private $defaults;

    /**
     * @param array $resources
     * @param array $defaults
     */
    public function __construct(array $resources, $defaults)
    {
        $this->resources = $resources;
        $this->defaults  = $defaults;
    }

    /**
     * @param array  $config
     * @param string $action
     *
     * @return string
     */
    private function getController($config, $action)
    {
        $controller = isset($config['controller']['id']) ? $config['controller']['id'] : 'resource.controller.abstract';

        return sprintf('%s:%s%s', $controller, $action, strpos($controller, '.') !== false ? 'Action' : '');
    }

    /**
     * @param array  $config
     * @param string $action
     *
     * @return array
     */
    private function getControllerConfiguration($config, $action)
    {
        $configutaion = isset($config['controller'][$action]) ? $config['controller'][$action] : array();
        !empty($this->defaults['controller']['security']) && $configutaion['security'] = $this->defaults['controller']['security'];
        !empty($config['controller']['security']) && $configutaion['security'] = $config['controller']['security'];
        !empty($config['controller']['redirect']) && $configutaion['redirect'] = $config['controller']['redirect'];

        return $configutaion;
    }

    /**
     * @param array $config
     *
     * @return bool
     */
    private function isControllerExist($config)
    {
        return isset($config['controller']) && !empty($config['controller']['id']);
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }

        $routes = new RouteCollection();

        foreach ($this->resources as $name => $config) {
            if (!$this->isControllerExist($config)) {
                continue;
            }


            $path = !empty($config['controller']['route']) ? $config['controller']['route'] : '/' . str_replace('.', '/', $name);
            $path = rtrim($this->defaults['base_url'], '/') . $path;
            $id   = $this->defaults['route_prefix'] . '.' . $name;

            $routes->add($id . '.index', new Route($path, array(
                '_controller'    => $this->getController($config, 'index'),
                '_configuration' => $this->getControllerConfiguration($config, 'index')
            )));

            $routes->add($id . '.edit', new Route($path . '/edit/{id}', array(
                '_controller'    => $this->getController($config, 'edit'),
                '_configuration' => $this->getControllerConfiguration($config, 'edit')
            )));

            $routes->add($id . '.add', new Route($path . '/add', array(
                '_controller'    => $this->getController($config, 'edit'),
                'id'             => 0,
                '_configuration' => $this->getControllerConfiguration($config, 'edit')
            )));

            $routes->add($id . '.delete', new Route($path . '/delete/{id}', array(
                '_controller'    => $this->getController($config, 'delete'),
                '_configuration' => $this->getControllerConfiguration($config, 'delete')
            )));
        }

        return $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return $type == 'resources';
    }

    /**
     * {@inheritdoc}
     */
    public function getResolver()
    {
        // needed, but can be blank, unless you want to load other resources
        // and if you do, using the Loader base class is easier (see below)
    }

    /**
     * {@inheritdoc}
     */
    public function setResolver(LoaderResolverInterface $resolver)
    {
        // same as above
    }
}