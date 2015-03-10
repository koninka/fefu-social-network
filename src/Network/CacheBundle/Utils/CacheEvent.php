<?php
namespace Network\CacheBundle\Utils;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;

/**
 * Created by PhpStorm.
 * User: user
 * Date: 05.03.2015
 * Time: 11:09
 */

class CacheEvent extends Event
{
    private $proxyCache;
    private $request;
    private $response;

    public function __construct(HttpCache $proxyCache, Request $request)
    {
        $this->proxyCache = $proxyCache;
        $this->request = $request;
    }

    public function getProxyCache()
    {
        return $this->proxyCache;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
        $this->stopPropagation();
    }
}
