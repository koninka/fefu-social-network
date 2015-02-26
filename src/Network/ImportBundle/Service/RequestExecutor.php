<?php
namespace Network\ImportBundle\Service;

use Application\Sonata\MediaBundle\Entity\Media;
use Buzz\Exception\RequestException;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;

/**
 * Created by PhpStorm.
 * User: user
 * Date: 10.02.2015
 * Time: 1:00
 */

class RequestExecutor extends ContainerAware
{
    protected $container;
    const contentNotChanged = 304;

    function __construct($container)
    {
        $this->container = $container;
    }

    public function request($method, $url, $headers, $params)
    {
        $em = $this->container->get('doctrine')->getManager();
        $curl = curl_init();
        $formattedUrl = strtr($url, $params);
        curl_setopt($curl, CURLOPT_URL, $formattedUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if (!empty($headers)) {
            $params = array();
            foreach ($headers as $header => $value) {
                array_push($params, $header.': '.$value);
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, $params);
        }
        $res = curl_exec($curl);
        $info = curl_getinfo($curl);
        $http_code = $info['http_code'];
        $header = substr($res, 0, $info['header_size']);
        $body = substr($res, $info['header_size']);
        $headers_arr = self::http_parse_headers($header);
        $etag = null;
        foreach ($headers_arr as $h) {
            if (isset($h[0]) && $h[0] == 'ETag') {
                $etag = $h[1];
            }
        }
        curl_close($curl);
        $ret = array();
        if (null !== $etag) {
            $ret['etag'] = $etag;
        }
        if ($http_code == static::contentNotChanged) {
            $body = array();
        }
        $ret['response'] = $body;
        return $ret;
    }

    function http_parse_headers($header)
    {
        $parsed = array_map(function($x) {
            return array_map("trim", explode(":", $x, 2)); },
            array_filter(array_map("trim", explode("\n", $header))));

        return $parsed;
    }

}
