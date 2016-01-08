<?php
namespace Igdr\Bundle\ResourceBundle\Controller;

use Igdr\Bundle\ManagerBundle\Model\ManagerFactoryInterface;
use Igdr\Bundle\ManagerBundle\Model\ManagerFactoryTrait;
use Symfony\Component\HttpFoundation\RequestStack;

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
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param RequestStack $requestStack
     * @param array        $defaults
     */
    public function __construct(RequestStack $requestStack, array $defaults = array())
    {
        $this->defaults     = $defaults;
        $this->requestStack = $requestStack;
    }

    /**
     * @return Configuration
     */
    public function create()
    {
        $config = $this->getConfiguration() + $this->getDefaults();

        $configuration = new Configuration();
        $configuration->setManagerFactory($this->managerFactory);
        $configuration->setBundle($this->getBundle());
        $configuration->setResource($this->getResource());
        isset($config['form']) && $configuration->setForm($config['form']);
        isset($config['manager']) && $configuration->setManager($config['manager']);
        isset($config['template']) && $configuration->setTemplate($config['template']);
        isset($config['security']) && $configuration->setSecurity($config['security']);
        isset($config['redirect']) && $configuration->setRedirect($config['redirect']);

        return $configuration;
    }

    /**
     * @return string
     */
    private function getBundle()
    {
        $chunks = explode('.', $this->requestStack->getMasterRequest()->attributes->get('_route'));

        return $chunks[1];
    }

    /**
     * @return string
     */
    private function getResource()
    {
        $chunks = explode('.', $this->requestStack->getMasterRequest()->attributes->get('_route'));

        return $chunks[2];
    }

    /**
     * @return array
     */
    private function getConfiguration()
    {
        return (array) $this->requestStack->getMasterRequest()->attributes->get('_configuration');
    }

    /**
     * @return array
     */
    private function getDefaults()
    {
        $chunks = explode('.', $this->requestStack->getMasterRequest()->attributes->get('_route'));
        $action = array_pop($chunks);
        if ($action == 'add') {
            $action = 'edit';
        }

        return isset($this->defaults['controller'][$action]) ? $this->defaults['controller'][$action] : array();
    }
}