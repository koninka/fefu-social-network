<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 05.03.2015
 * Time: 11:14
 */
namespace Network\CacheBundle\Service;

use Network\CacheBundle\Subscriber\Events;
use Network\CacheBundle\Utils\CacheEvent;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

abstract class ReverseProxyCache extends HttpCache
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function getEventDispatcher()
    {
        if (null === $this->eventDispatcher) {
            $this->eventDispatcher = new EventDispatcher();
        }

        return $this->eventDispatcher;
    }

    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->getEventDispatcher()->addSubscriber($subscriber);
    }

    public function fetch(Request $request, $catch = false)
    {
        return parent::fetch($request, $catch);
    }

    public function invalidate(Request $request, $catch = false)
    {
        if ($this->getEventDispatcher()->hasListeners(Events::PRE_INVALIDATE)) {
            $event = new CacheEvent($this, $request);
            $this->getEventDispatcher()->dispatch(Events::PRE_INVALIDATE, $event);
            if ($event->getResponse()) {
                return $event->getResponse();
            }
        }

        return parent::invalidate($request, $catch);
    }
}
