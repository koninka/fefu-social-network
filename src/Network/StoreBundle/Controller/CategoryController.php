<?php

namespace Network\StoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
        $categoryService = $this->get('network.store.category_service');
        $json = $categoryService->getChildrenNamedLike(
            $categoryClass,
            $parentCategoryId,
            $query,
            $page,
            $limit
        );

        return new JsonResponse($json);
    }
}
