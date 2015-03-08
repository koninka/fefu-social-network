<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 16.02.2015
 * Time: 23:03
 */

namespace Network\ImportBundle\Utils;


class InstagramImportConfig {
    public $album;

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
