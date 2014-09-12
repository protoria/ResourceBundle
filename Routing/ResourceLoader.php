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
     * @param array $resources
     */
    public function __construct(array $resources)
    {
        $this->resources = $resources;
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
            $path = '/admin/' . str_replace('.', '/', $name);
            $id   = 'admin.' . $name;

            $routes->add($id . '.index', new Route($path, array(
                '_controller'    => 'resource.controller.abstract:indexAction',
                '_configuration' => isset($config['controller']['index']) ? $config['controller']['index'] : array()
            )));

            $routes->add($id . '.edit', new Route($path . '/edit/{id}', array(
                '_controller'    => 'resource.controller.abstract:editAction',
                '_configuration' => isset($config['controller']['edit']) ? array($config['controller']['edit']) : array()
            )));

            $routes->add($id . '.add', new Route($path . '/add', array(
                '_controller'    => 'resource.controller.abstract:editAction',
                'id'             => 0,
                '_configuration' => isset($config['controller']['edit']) ? $config['controller']['edit'] : array()
            )));

            $routes->add($id . '.delete', new Route($path . '/delete/{id}', array(
                '_controller'    => 'resource.controller.abstract:deleteAction',
                '_configuration' => isset($config['controller']['delete']) ? $config['controller']['delete'] : array()
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