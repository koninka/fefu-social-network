<?php
namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="files")
 */
class MP3File
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="path", length=500)
     */
    private $path;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="MP3Record", mappedBy="file")
     */
    private $records;

    public function __construct()
    {
        $this->records = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return MP3File
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     *
     * @return MP3File
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getRecords()
    {
        return $this->records;
    }

    /**
     * @param ArrayCollection $records
     *
     * @return MP3File
     */
    public function setRecords($records)
    {
        $this->records = $records;

        return $this;
    }

    public function addRecord($record)
    {
        $this->records->add($record);
    }

    public function removeRecord($record)
    {
        $this->records->removeElement($record);
    }

} 