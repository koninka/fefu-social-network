<?php

namespace Network\ImportBundle\Controller;

use Monolog\Registry;
use Network\StoreBundle\Entity\SyncTask;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Exception;

class ImportController extends Controller
{
	public function indexAction()
    {
        return $this->render('NetworkImportBundle::import_page.html.twig', array('user' => $this->getUser()));
    }

    private function constructConfigClass($owner)
    {
        $class = 'Network\\ImportBundle\\Utils\\' . ucfirst($owner) . 'ImportConfig';
        if (!class_exists($class)) {
            throw new Exception('UnknownConfigException');
        }

        return $class;
    }

    private function constructFormClass($owner)
    {
        $class = 'Network\\ImportBundle\\Form\\' . ucfirst($owner) . 'ConfigType';
        if (!class_exists($class)) {
            throw new Exception('UnknownFormException');
        }

        return $class;
    }

    public function configAction(Request $request, $service)
    {
        $endpoints = $this->container->getParameter('endpoints');
        $formAction = $this->container->getParameter('config_import_path') . $service;
        $configClass = self::constructConfigClass($service);
        $formClass = self::constructFormClass($service);
        $config = new $configClass();
        $url = 'NetworkImportBundle::' . $service . '_import_config.html.twig';
        $form = $this->createForm(new $formClass($this->get('security.context'), $this->get('doctrine'), $config));
        $params = array(
            'user' => $this->getUser(),
            'form' => $form->createView(),
            'form_action' => $formAction
        );
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $config = $form->getData();
                $em = $this->getDoctrine()->getManager();
                foreach ($endpoints as $key => $endpoint) {
                    if ($endpoint['owner'] == $service && isset($endpoint['config'])) {
                        foreach ($endpoint['config'] as $k => $c) {
                            $setter = 'set' . ucfirst($k);
                            method_exists($config, $setter) ? $config->$setter($c) : 1;
                        }
                    }
                    self::registerSyncTasks($service, $key, $endpoint, $config);
                }
                $url =  'NetworkImportBundle::import_wait.html.twig';
            }
        }

        return $this->render($url, $params);
    }

    public function registerSyncTasks($service, $key, $endpoint, $config)
    {
        $configs = get_object_vars($config);;
        $em = $this->getDoctrine()->getManager();
        if ($endpoint['owner'] == $service) {
            $task = new SyncTask();
            $task->setEndpoint($key)
                ->setUserId($this->getUser()->getId())
                ->setParams($configs)
                ->setLastUpdateTimestamp(0)
                ->setOffset(0)
                ->setIfNoneMatch(0);
            $tasks = $em->createQueryBuilder()
                ->select('u')
                ->from('NetworkStoreBundle:SyncTask', 'u')
                ->andWhere('u.endpoint = :key')
                ->setParameter('key', $key)
                ->andWhere('u.userId = :id')
                ->setParameter('id', $this->getUser()->getId())
                ->getQuery()
                ->getResult();
            if (empty($tasks)) {
                $em->persist($task);
            }
        }
        $em->flush();
        $em->clear();
    }
}
