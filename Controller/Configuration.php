<?php
namespace Igdr\Bundle\ResourceBundle\Controller;

use Igdr\Bundle\ManagerBundle\Manager\AbstractManager;
use Igdr\Bundle\ManagerBundle\Manager\ManagerFactory;

/**
 * Base controller configurator
 */
class Configuration
{
    /**
     * @var string
     */
    private $bundle;

    /**
     * @var string
     */
    private $resource;

    /**
     * @var string
     */
    private $template;

    /**
     * @var string
     */
    private $pageTitle;

    /**
     * @var AbstractManager
     */
    private $manager;

    /**
     * @var string
     */
    private $grid;

    /**
     * @var string
     */
    private $form;

    /**
     * @var array
     */
    private $security;

    /**
     * @var ManagerFactory
     */
    private $managerFactory;

    /**
     * @var array
     */
    private $redirect;

    /**
     * @param \Igdr\Bundle\ManagerBundle\Manager\ManagerFactory $managerFactory
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
     *
     * @return $this
     */
    public function setBundle($bundle)
    {
        $this->bundle = $bundle;

        return $this;
    }

    /**
     * @return string
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * @param string $resource
     *
     * @return $this
     */
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param string $pageTitle
     *
     * @return $this
     */
    public function setPageTitle($pageTitle)
    {
        $this->pageTitle = $pageTitle;

        return $this;
    }

    /**
     * @return string
     */
    public function getPageTitle()
    {
        return $this->pageTitle ? $this->pageTitle : sprintf('admin.%s.%s.title', $this->bundle, $this->resource);
    }

    /**
     * @param string $template
     *
     * @return Configuration
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param AbstractManager $manager
     *
     * @return $this
     */
    public function setManager(AbstractManager $manager)
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * @return AbstractManager
     */
    public function getManager()
    {
        return $this->managerFactory->get($this->manager ? $this->manager : sprintf('%s.manager.%s', $this->bundle, $this->resource));
    }

    /**
     * @param string $form
     *
     * @return $this
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @return string
     */
    public function getForm()
    {
        return $this->form ? $this->form : sprintf('%s_%s', $this->bundle, $this->resource);
    }

    /**
     * @param string $grid
     *
     * @return $this
     */
    public function setGrid($grid)
    {
        $this->grid = $grid;

        return $this;
    }

    /**
     * @return string
     */
    public function getGrid()
    {
        return $this->grid ? $this->grid : sprintf('%s.grid.%s', $this->bundle, $this->resource);
    }

    /**
     * @param array $access
     *
     * @return $this
     */
    public function setSecurity($access)
    {
        $this->security = $access;

        return $this;
    }

    /**
     * @return array
     */
    public function getSecurity()
    {
        return $this->security;
    }

    /**
     * @param array $redirect
     *
     * @return $this
     */
    public function setRedirect($redirect)
    {
        $this->redirect = $redirect;

        return $this;
    }

    /**
     * @return array
     */
    public function getRedirect()
    {
        return $this->redirect;
    }
}