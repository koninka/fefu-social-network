<?php

namespace Network\StoreBundle\Service;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Doctrine\ORM\EntityManager;

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
        $items = $query->getResult();
        if ($callback == null) {
            $callback = function ($item) {
                return ['id' => $item->getId(), 'text' => (string) $item];
            };
        }
        $data = array_map($callback, $items);
        $more = count($data) >= $limit;

        return [
            'items' => $data,
            'itemsPerPage' => $limit,
            'more' => $more
        ];
    }
}