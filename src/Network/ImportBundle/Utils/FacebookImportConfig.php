<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 28.02.2015
 * Time: 12:02
 */

namespace Network\ImportBundle\Utils;



class FacebookImportConfig {
    public $album;
    public $type;

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getAlbum()
    {
        return $this->album;
    }

    /**
     * @param mixed $album
     */
    public function setAlbum($album)
    {
        $this->album = $album;

        return $this;
    }
}