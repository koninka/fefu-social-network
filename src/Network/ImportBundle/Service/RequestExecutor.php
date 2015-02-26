<?php
namespace Network\ImportBundle\Service;

use Application\Sonata\MediaBundle\Entity\Media;
use Buzz\Exception\RequestException;
use Network\ImportBundle\Exceptions\GetItemContentException;
use Network\ImportBundle\Exceptions\UnknownItemException;
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

    function __construct($container)
    {
        $this->container = $container;
    }

    public function request($method, $url, $headers, $params)
    {
       /* $buzz = $this->container->get('buzz');
        $response = new Response();
        switch ($method) {
            case 'GET':
                $formattedUrl = strtr($url, $params);
                $response = $buzz->get($formattedUrl);
                break;
        }*/
        $curl = curl_init();
        $formattedUrl = strtr($url, $params);
        curl_setopt($curl, CURLOPT_URL, $formattedUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }

}
