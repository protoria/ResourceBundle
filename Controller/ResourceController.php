<?php
namespace Igdr\Bundle\ResourceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Abstract controller for admin
 */
class ResourceController extends Controller
{
    /**
     * @var string
     */
    const CMD_APPLY = 'apply';

    /**
     * @var string
     */
    const CMD_CREATE_ANOTHER = 'create_another';

    /**
     * @var Configuration
     */
    private $configuration = null;

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        if ($this->configuration === null) {
            $this->configuration = $this->get('resource.controller.configuration_factory')->create();
        }

        return $this->configuration;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function indexAction(Request $request)
    {
        $configuration = $this->getConfiguration();

        if (!$this->get('security.context')->isGranted($configuration->getSecurity())) {
            throw new AccessDeniedHttpException();
        }

        $grid = $this->get('widget_grid_factory')->createGrid($this->get($configuration->getGrid())->setManager($configuration->getManager()));
        $grid->setBaseUrl($this->createUrl($request, 'index'));
        $grid->getStorage()->load();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(array('success' => true, 'grid' => $grid->render()));
        }

        //crumbs
        $this->createCrumbs($request);

        //render
        return $this->render(
            $configuration->getTemplate(),
            array(
                'grid'          => $grid,
                'createUrl'     => $this->createUrl($request, 'add'),
                'page'          => 'index',
                'configuration' => $configuration
            )
        );
    }

    /**
     * @param integer $id
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function editAction($id, Request $request)
    {
        $configuration = $this->getConfiguration();

        if (!$this->get('security.context')->isGranted($configuration->getSecurity())) {
            throw new AccessDeniedHttpException();
        }

        //get data
        $entity = $this->getEntity($id, $request);

        //create form
        $form = $this->createForm($configuration->getForm(), $entity);

        //save
        if ($request->isMethod('POST')) {
            $form->submit($request);
            if ($form->isValid()) {
                //save entity
                $configuration->getManager()->save($form->getData());

                //notice
                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('admin.form.message.success.text'));

                //redirect
                return $this->redirect($this->getRedirectUrl($request, $entity->getId()));
            } else {
                $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('admin.form.message.error.text'));
            }
        }

        //crumbs
        $this->createCrumbs($request, $entity && $entity->getId() ? 'edit' : 'add');

        //render
        return $this->render(
            $configuration->getTemplate(),
            array(
                'form'          => $form->createView(),
                'backUrl'       => $this->createUrl($request, 'index'),
                'page'          => $entity && $entity->getId() ? 'edit' : 'add',
                'configuration' => $configuration,
                'entity'        => $entity
            )
        );
    }

    /**
     * @param int     $id
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function deleteAction($id, Request $request)
    {
        $configuration = $this->getConfiguration();

        if (!$this->get('security.context')->isGranted($configuration->getSecurity())) {
            throw new AccessDeniedHttpException();
        }

        $configuration->getManager()->delete($id);

        return $this->redirect($this->createUrl($request, 'index'));
    }

    /**
     * @param int     $id
     * @param Request $request
     *
     * @return null|object
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function getEntity($id, Request $request)
    {
        //get data
        $manager = $this->getConfiguration()->getManager();
        if ($id) {
            $manager->setId($id);
            $entity = $manager->findOne();
            if ($entity == null) {
                throw $this->createNotFoundException('Entity not found');
            }
        } else {
            $entity = $manager->create();
        }

        return $entity;
    }

    /**
     * @param Request $request
     * @param string  $action
     */
    protected function createCrumbs(Request $request, $action = '')
    {
        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem($this->getConfiguration()->getPageTitle() . '.index', $this->createUrl($request, 'index'));

        if ($action) {
            $breadcrumbs->addItem($this->getConfiguration()->getPageTitle() . '.' . $action);
        }
    }

    /**
     * @param Request $request
     * @param string  $action
     * @param array   $params
     *
     * @return string
     */
    protected function createUrl(Request $request, $action, $params = array())
    {
        $redirectConfig = $this->configuration->getRedirect();
        if ($action == 'index' && !empty($redirectConfig)) {
            return $this->generateUrl($redirectConfig['route'], $this->parse($redirectConfig['parameters'], $request));
        }

        $route = $request->attributes->get('_route');
        $arr   = explode('.', $route);
        array_pop($arr);
        $arr[] = $action;

        return $this->get('router')->generate(join('.', $arr), $params, true);
    }

    /**
     * @param Request $request
     * @param integer $id
     *
     * @return string
     */
    private function getRedirectUrl(Request $request, $id)
    {
        if ($request->get('cmd') == self::CMD_APPLY && $id) {
            $url = $this->createUrl($request, 'edit', ['id' => $id]);
        } elseif ($request->get('cmd') == self::CMD_CREATE_ANOTHER) {
            $url = $this->createUrl($request, 'add');
        } else {
            $url = $this->createUrl($request, 'index');
        }

        return $url;
    }

    /**
     * @param array   $parameters
     * @param Request $request
     *
     * @return array
     */
    public function parse(array $parameters, Request $request)
    {
        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                $parameters[$key] = $this->parse($value, $request);
            }
            if (is_string($value) && 0 === strpos($value, '$')) {
                $parameterName    = substr($value, 1);
                $parameters[$key] = $request->get($parameterName);
            }
            if (is_string($value) && 0 === strpos($value, 'expr:')) {
                $parameters[$key] = $this->expression->evaluate(substr($value, 5));
            }
        }

        return $parameters;
    }
}