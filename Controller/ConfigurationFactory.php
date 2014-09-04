<?php
namespace Igdr\Bundle\ResourceBundle\Controller;

use Igdr\Bundle\ManagerBundle\Manager\ManagerFactory;

/**
 * Class ConfigurationFactory
 */
class ConfigurationFactory
{
    /**
     * @var ManagerFactory
     */
    private $managerFactory;

    /**
     * @param ManagerFactory $managerFactory
     *
     * @return $this
     */
    public function setManagerFactory($managerFactory)
    {
        $this->managerFactory = $managerFactory;

        return $this;
    }

    /**
     * @param string $bundle
     * @param string $resource
     * @param array  $config
     *
     * @return Configuration
     */
    public function create($bundle, $resource, array $config)
    {
        $configuration = new Configuration();
        $configuration->setManagerFactory($this->managerFactory);
        $configuration->setBundle($bundle);
        $configuration->setResource($resource);
        isset($config['form_type']) && $configuration->setForm($config['form_type']);
        isset($config['manager']) && $configuration->setManager($config['manager']);
        isset($config['template']) && $configuration->setTemplateIndex($config['template']);
        isset($config['template']) && $configuration->setTemplateUpdate($config['template']);

        return $configuration;
    }
}