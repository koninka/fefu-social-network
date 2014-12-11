<?php

namespace Network\StoreBundle\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination;
class CategoryService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Paginator
     */
    private $paginator;

    /**
     * @param EntityManager $em
     */
    public function __construct($em)
    {
        $this->em = $em;
    }

    /**
     * @param Paginator $paginator
     *
     * @return $this
     */
    public function setPaginator($paginator)
    {
        $this->paginator = $paginator;

        return $this;
    }

    /**
     * @param string $childClass
     * @param int    $parentId
     * @param string $like
     * @param int    $page
     * @param int    $limit
     *
     * @return array
     */
    public function getChildrenNamedLike($childClass, $parentId, $like, $page, $limit)
    {
        $parentClass = strtolower($childClass::getParent());
        $q = $this->em->createQueryBuilder()
            ->select('u')
            ->from($childClass, 'u')
            ->join("u.$parentClass", 'p')
            ->where("p.id = :parentId")
            ->andWhere("u.name LIKE :q")
            ->orderBy('u.id')
            ->setParameter('parentId', $parentId)
            ->setParameter('q', '%'. $like .'%');

        return $this->paginator->getPaginatedResult($q, $page, $limit);
    }
}