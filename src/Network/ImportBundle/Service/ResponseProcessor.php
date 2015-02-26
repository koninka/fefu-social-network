<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 17.02.2015
 * Time: 22:15
 */

namespace Network\ImportBundle\Service;

use Exception;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\HttpFoundation\File\File;

class ResponseProcessor
{
    protected $doctrine;
    protected $batchSize = 50;
    protected $container;

    function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    private function constructItemClass($owner)
    {
        $class = 'Network\\StoreBundle\\Entity\\'.ucfirst($owner) . 'Item';
        if (!class_exists($class)) {
            throw new Exception('UnknownItemException');
        }

        return $class;
    }

    private function constructSetterName($field)
    {
        return 'set' . preg_replace_callback(
                    '/(_\w)/i',
                   function ($match) {
                   return strtoupper(substr($match[0], 1));
        },
        '_' . $field
        );
    }

    public function process($owner, $response, $config, $jsonRoot)
    {
        $mediaClass = self::constructItemClass($owner);
        $medias = array();
        $json = json_decode($response, true);
        if (!isset($json[$jsonRoot])) {
            throw new Exception('WrongRootException');
        }
        $data = $json[$jsonRoot];
        foreach ($data as $i => $item) {
            if (!is_array($item)) {
                continue;
            }
            $mediaItem = new $mediaClass();
            foreach ($item as $key => $field) {
                $setter = self::constructSetterName($key);
                method_exists($mediaItem, $setter) ? $mediaItem->$setter($field) : 1;
            }
            foreach ($config as $k => $param) {
                $setter = 'set'.ucfirst($k);
                method_exists($mediaItem, $setter) ? $mediaItem->$setter($param) : 1;
            }
            method_exists($mediaItem, 'setStatus') ? $mediaItem->setStatus(0) : 1;
            $medias[$i] = $mediaItem;
        }
        self::insert($medias);

        return count($medias);
    }

    public function insert($items)
    {
        $em = $this->doctrine->getManager();
        foreach ($items as $i => $item) {
            $em->persist($item);
            if (($i % $this->batchSize) == 0) {
                $em->flush();
                $em->clear();
            }
        }
        $em->flush();
        $em->clear();
    }

}
