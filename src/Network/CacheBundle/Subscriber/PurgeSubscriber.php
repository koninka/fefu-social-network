<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 05.03.2015
 * Time: 11:26
 */
namespace Network\CacheBundle\Subscriber;


use Network\CacheBundle\Utils\CacheEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PurgeSubscriber implements EventSubscriberInterface
{
    private $options = array();

    public function __construct(array $options = array())
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'purge_method' => 'PURGE',
        ));
        $this->options = $resolver->resolve($options);
    }

    public static function getSubscribedEvents()
    {
        return array(
            Events::PRE_INVALIDATE => 'handlePurge',
        );
    }

    public function handlePurge(CacheEvent $event)
    {
        $request = $event->getRequest();
        if ($this->options['purge_method'] !== $request->getMethod()) {
            return;
        }
        $response = new Response();
        if ($event->getProxyCache()->getStore()->purge($request->getUri())) {
            $response->setStatusCode(200, 'Purged');
        } else {
            $response->setStatusCode(200, 'Not found');
        }
        $event->setResponse($response);
    }
}