<?php
namespace Network\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class LikeController extends Controller
{

    public function likeAction(Request $request)
    {
        $result = [];
        try {
           $result = $this->get('network.store.like_service')
                ->like($this->getUser(), json_decode($request->getContent(), true));
        } catch (Exception $e) {
           $result['error'] = $e->getMessage();
        }

        return new JsonResponse($result);
    }

    public function getCountAction(Request $request)
    {
        $result = [];
        try {
          $result = $this->get('network.store.like_service')
            ->countLike(json_decode($request->getContent(), true));
        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }
            return new JsonResponse($result);
    }
    
     public function getUserAction(Request $request)
    {
        $result = [];
        try {
          $result = $this->get('network.store.like_service')
            ->userLike(json_decode($request->getContent(), true));
        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }
        
        return new JsonResponse($result);
    }

}
