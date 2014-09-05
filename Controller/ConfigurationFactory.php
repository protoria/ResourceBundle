<?php
namespace Igdr\Bundle\ResourceBundle\Controller;

use Igdr\Bundle\ManagerBundle\Model\ManagerFactoryInterface;
use Igdr\Bundle\ManagerBundle\Model\ManagerFactoryTrait;

/**
 * Class ConfigurationFactory
 */
class ConfigurationFactory implements ManagerFactoryInterface
{
    use ManagerFactoryTrait;

    /**
     * @var array
     */
    private $defaults;

    /**
     * @param array $defaults
     */
    public function __construct(array $defaults = array())
    {
        $this->defaults = $defaults;
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
        $config = $config + $this->defaults;

        $configuration = new Configuration();
        $configuration->setManagerFactory($this->managerFactory);
        $configuration->setBundle($bundle);
        $configuration->setResource($resource);
        isset($config['form_type']) && $configuration->setForm($config['form_type']);
        isset($config['manager']) && $configuration->setManager($config['manager']);
        isset($config['form']['index']['template']) && $configuration->setTemplateIndex($config['form']['index']['template']);
        isset($config['form']['edit']['template']) && $configuration->setTemplateUpdate($config['form']['edit']['template']);

        return $configuration;
    }
}