<?php

namespace Network\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Network\StoreBundle\Entity\User;

trait ProfileTrait
{
    /**
     * @param string $view
     * @param array $parameters
     * @param Response $response
     * @return Response
     */
    public function render($view, array $parameters = array(), Response $response = null)
    {
        //get global vars
        $rels = $this->getDoctrine()->getRepository('NetworkStoreBundle:Relationship');
        $friendshipRequestsCount = 0;
        $communityRequestsCount = 0;
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            $curUser = $this->getUser();
            $friendshipRequestsCount = $rels->getFriendshipRequestsForUserCount($curUser->getId());
            $communityService = $this->get('network.store.community_service');
            $communityRequestsCount = count($communityService->getUserInviteeById($curUser));
        }
        if ($friendshipRequestsCount > 0) {
            $parameters['friendship_requests_count'] = $friendshipRequestsCount;
        }
        if ($communityRequestsCount > 0) {
            $parameters['community_requests_count'] = $communityRequestsCount;
        }

        return $this->container->get('templating')->renderResponse($view, $parameters, $response);
    }

}
