<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 05.03.2015
 * Time: 11:30
 */
namespace Network\CacheBundle\Subscriber;

use Network\CacheBundle\Utils\CacheEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RefreshSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            Events::PRE_HANDLE => 'handleRefresh',
        );
    }

    public function handleRefresh(CacheEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->isMethodSafe()
            || !$request->isNoCache()
        ) {
            return;
        }
        $event->setResponse(
            $event->getProxyCache()->fetch($request)
        );
    }
}
