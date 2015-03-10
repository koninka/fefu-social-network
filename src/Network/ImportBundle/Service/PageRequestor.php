<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 02.03.2015
 * Time: 15:02
 */

namespace Network\ImportBundle\Service;


use Doctrine\ORM\Tools\Pagination\Paginator;

class PageRequestor {
    protected  function countPages($qb, $limit)
    {
        $res = 0;
        $count = $qb->getQuery()
                    ->getSingleScalarResult();
        if (!$count) {
            return 0;
        }
        $res = round($count / $limit);
        $res += $count % $limit != 0;

        return $res;
    }

    protected  function paginate($dql, $pageSize = 10, $currentPage = 1)
    {
        $paginator = new Paginator($dql);
        $paginator->getQuery()
            ->setFirstResult($pageSize * ($currentPage - 1)) // set the offset
            ->setMaxResults($pageSize); // set the limit

        return $paginator;
    }
}
