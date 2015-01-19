<?php

namespace Network\UserBundle\Controller;

use Network\StoreBundle\DBAL\RelationshipStatusEnumType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Network\StoreBundle\Entity\Relationship;

class FriendController extends Controller
{
    use ProfileTrait;
    
    public function sendFriendshipRequestAction($id)
    {
        return $this->render('NetworkWebBundle:User:msg.html.twig', [
                'msg' => 'msg' . $this->get('network_store.relationship_manager')->sendFriendshipRequest($id)
            ]);
    }

    public function acceptFriendshipRequestAction($id)
    {
        return $this->render('NetworkWebBundle:User:msg.html.twig', [
                'msg' => 'msg' . $this->get('network_store.relationship_manager')->acceptFriendshipRequest($id)
            ]);
    }

    public function declineFriendshipAction($id)
    {
        return $this->render('NetworkWebBundle:User:msg.html.twig', [
                'msg' => 'msg' . $this->get('network_store.relationship_manager')->declineFriendship($id)
            ]);
    }

    public function deleteFriendshipAction($id)
    {
        return $this->render('NetworkWebBundle:User:msg.html.twig', [
                'msg' => 'msg' . $this->get('network_store.relationship_manager')->deleteFriendship($id)
            ]);
    }

    public function deleteFriendshipSubscriptionAction($id)
    {
        return $this->render('NetworkWebBundle:User:msg.html.twig', [
                'msg' => 'msg' . $this->get('network_store.relationship_manager')->deleteFriendshipSubscription($id)
            ]);
    }

}
