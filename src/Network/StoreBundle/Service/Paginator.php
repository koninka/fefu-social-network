<?php

namespace Network\StoreBundle\Service;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

class Paginator
{

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct($em)
    {
        $this->em = $em;
    }

    /**
     * @param Query|QueryBuilder $query
     * @param int                $page
     * @param int                $limit
     * @param Closure            $callback
     *
     * @return array
     */
    public function getPaginatedResult($query, $page ,$limit, $callback = null)
    {
        $query->setFirstResult(($page -1) * $limit)
            ->setMaxResults($limit);
        if ($query instanceof QueryBuilder) {
            $query = $query->getQuery();
        }
        $paginator = new DoctrinePaginator($query, $fetchJoinCollection = true);
        $count = count($paginator);
        $items = $query->getResult();
        if ($callback == null) {
            $callback = function ($item) {
                return ['id' => $item->getId(), 'text' => (string) $item];
            };
        }
        $data = array_map($callback, $items);

        return [
            'totalCount' => $count,
            'items' => $data,
            'itemsPerPage' => $limit
        ];
    }
}