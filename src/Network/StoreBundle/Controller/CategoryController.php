<?php

namespace Network\StoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

/**
 * Class CategoryController
 * Used to update selects via ajax
 */
class CategoryController extends Controller
{

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function getItemsFromCategoryAction(Request $request)
    {
        $categoryName = $request->query->get('category');
        $parentCategoryId = $request->query->get('parentId');
        $query = $request->query->get('query', '');
        $page = $request->query->get('page', 1);
        $limit = 30;//$request->query->get('l', 10);
        $categoryClass = 'Network\\StoreBundle\\Entity\\' . $categoryName;
        if (!method_exists($categoryClass, 'getParent')) {
            return new JsonResponse(['message' => 'wrong value in "category"'], 400);
        }
        $parentCategory = strtolower($categoryClass::getParent());

        $manager = $this->get('doctrine.orm.entity_manager');
        $q = $manager->createQueryBuilder()
            ->select('u')
            ->from($categoryClass, 'u')
            ->join("u.$parentCategory", 'p')
            ->where("p.id = :parentId")
            ->andWhere("u.name LIKE :q")
            ->orderBy('u.id')
            ->setParameter('parentId', $parentCategoryId)
            ->setParameter('q', '%'. $query .'%')
            ->setFirstResult(($page -1) * $limit)->setMaxResults($limit)
            ->getQuery();
        $paginator = new DoctrinePaginator($q, $fetchJoinCollection = true);
        $count = count($paginator);
        $items = $q->getResult();
        $data = [];
        foreach ($items as $i) {
            $data[] = ['id' => $i->getId(), 'text' => (string) $i];
        }
        $json = [
            'totalCount' => $count,
            'items' => $data,
            'itemsPerPage' => $limit
        ];

        return new JsonResponse($json);
    }
}
