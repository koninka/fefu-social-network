<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 05.03.2015
 * Time: 11:04
 */

namespace Network\CacheBundle\Service;

require_once __DIR__.'/../../../../app/AppCache.php';

use AppCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CacheManager
{
    private $cache;
    private $urlGenerator;
    private $generateUrlType = UrlGeneratorInterface::ABSOLUTE_PATH;
    const HOST = 'http://vdolgah.org';

    public function __construct(\AppKernel $kernel, UrlGeneratorInterface $urlGenerator)
    {
        $this->cache = new AppCache($kernel, null);
        $this->urlGenerator = $urlGenerator;
    }

    public function invalidate($url)
    {
        if (strpos($url, '?')) {
            $url = stristr($url, '?', true);
        }
        $this->cache->invalidate(Request::create($url, 'PURGE'));
    }

    public function invalidateDependencies($dependencies, array $parameters = array())
    {
        foreach ($dependencies as $d) {
            self::invalidate(static::HOST . $this->urlGenerator->generate($d, $parameters, $this->generateUrlType));
        }
    }

    public function refreshRoute($request)
    {
        $this->cache->handle($request);

        return $this;
    }
}
