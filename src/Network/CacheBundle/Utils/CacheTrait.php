<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 05.03.2015
 * Time: 1:37
 */

namespace Network\CacheBundle\Utils;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait CacheTrait
{
    public function notifyCacheInvalidate($route, $params = array())
    {
        $cacheManager = $this->get('network_cache.manager');
        $dependencies = $this->container->getParameter('cache_dependencies');
        if (!isset($dependencies[$route])) {
            return;
        }
        $cacheManager->invalidateDependencies($dependencies[$route]['dependent'], $params);
    }

    public function refreshRoute(Request $request)
    {
        $cacheManager = $this->get('network_cache.manager');
        $cacheManager->refreshRoute($request);
    }

    public function setCache($response)
    {
        $response->setEtag(md5(uniqid()))
            ->setPublic()
            ->setLastModified(new \DateTime())
            ->setMaxAge(600)
            ->setSharedMaxAge(600);

        return $response;
    }
}
