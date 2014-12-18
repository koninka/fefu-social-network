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
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $userFriend = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($id);
        $msg = 'msg.user_not_found';
        if (!$user) {
            $msg = 'msg.not_authorized';
        } elseif ($user->getId() == $id) {
            $msg = 'msg.same_user';
        } elseif (!empty($userFriend)) {
            $status = $user->getRelationshipStatus($id);
            if ($status === RelationshipStatusEnumType::FS_ACCEPTED) {
                $msg = 'msg.is_already_friend';
            } elseif ($status === RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME) {
                $msg = 'msg.is_already_subscribed';
            } elseif ($status === RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER) {
                $friendRelationship = $userFriend->getRelationship($user->getId());
                $friendRelationship->setStatus(RelationshipStatusEnumType::FS_ACCEPTED);
                $em->persist($friendRelationship);

                $relationship = $user->getRelationship($id);
                $relationship->setStatus(RelationshipStatusEnumType::FS_ACCEPTED);
                $em->persist($relationship);

                $em->flush();

                $msg = 'msg.friendship_accepted';
            } elseif ($status != RelationshipStatusEnumType::FS_NONE) {
                $msg = $status.'msg.unknown_error';
            } else {
                $relationship = new Relationship();
                $relationship->setUser($user)
                             ->setPartner($userFriend)
                             ->setStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME);
                $em->persist($relationship);

                $friendRelationship = new Relationship();
                $friendRelationship->setUser($userFriend)
                                   ->setPartner($user)
                                   ->setStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER);
                $em->persist($friendRelationship);

                $em->flush();

                $msg = 'msg.friendship_request_sent';
            }
        }

        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);
    }

    public function acceptFriendshipRequestAction($id)
    {
        $user = $this->getUser();
        $userFriend = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($id);
        $msg = 'msg.user_not_found';
        if (!$user) {
            $msg = 'msg.not_authorized';
        } elseif ($user->getId() == $id) {
            $msg = 'msg.same_user';
        } elseif (!empty($userFriend)) {
            $status = $userFriend->getRelationshipStatus($user->getId());
            if ($status === RelationshipStatusEnumType::FS_ACCEPTED) {
                $msg = 'msg.is_already_friend';
            } elseif ($status != RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME) {
                $msg = 'msg.user_does_not_sent_friendship_request';
            } else {
                $em = $this->getDoctrine()->getManager();

                $friendRelationship = $userFriend->getRelationship($user->getId());
                $friendRelationship->setStatus(RelationshipStatusEnumType::FS_ACCEPTED);
                $em->persist($friendRelationship);

                $relationship = $user->getRelationship($id);
                $relationship->setStatus(RelationshipStatusEnumType::FS_ACCEPTED);
                $em->persist($relationship);

                $em->flush();

                $msg = 'msg.friendship_accepted';
            }
        }

        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);
    }

    public function declineFriendshipAction($id)
    {
        $user = $this->getUser();
        $userFriend = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($id);
        $msg = 'msg.user_not_found';
        if (!$user) {
            $msg = 'msg.not_authorized';
        } elseif ($user->getId() == $id) {
            $msg = 'msg.same_user';
        } elseif (!empty($userFriend)) {
            $status = $userFriend->getRelationshipStatus($user->getId());
            if ($status === RelationshipStatusEnumType::FS_ACCEPTED) {
                $msg = 'msg.is_already_friend';
            } elseif ($status != RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME) {
                $msg = 'msg.user_does_not_sent_friendship_request';
            } else {
                $em = $this->getDoctrine()->getManager();

                $friendRelationship = $userFriend->getRelationship($user->getId());
                $friendRelationship->setStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME);
                $em->persist($friendRelationship);

                $relationship = $user->getRelationship($id);
                $relationship->setStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER);
                $relationship->setHidden(true);
                $em->persist($relationship);

                $em->flush();

                $msg = 'msg.friendship_request_declined';
            }
        }

        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);
    }

    public function deleteFriendshipAction($id)
    {
        $user = $this->getUser();
        $userFriend = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($id);
        $msg = 'msg.user_not_found';
        if (!$user) {
            $msg = 'msg.not_authorized';
        } elseif ($user->getId() == $id) {
            $msg = 'msg.same_user';
        } elseif (!empty($userFriend)) {
            $status = $userFriend->getRelationshipStatus($user->getId());
            if ($status != RelationshipStatusEnumType::FS_ACCEPTED) {
                $msg = 'msg.user_is_not_friend';
            } else {
                $em = $this->getDoctrine()->getManager();

                $friendRelationship = $userFriend->getRelationship($user->getId());
                $friendRelationship->setStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME);
                $em->persist($friendRelationship);

                $relationship = $user->getRelationship($id);
                $relationship->setStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER)
                             ->setHidden(true);
                $em->persist($relationship);

                $em->flush();

                $msg = 'msg.friendship_deleted';
            }
        }

        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);
    }

    public function deleteFriendshipSubscriptionAction($id)
    {
        $user = $this->getUser();
        $userFriend = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($id);
        $msg = 'msg.user_not_found';
        if (!$user) {
            $msg = 'msg.not_authorized';
        } elseif ($user->getId() == $id) {
            $msg = 'msg.same_user';
        } elseif (!empty($userFriend)) {
            $status = $userFriend->getRelationshipStatus($user->getId());
            if ($status != RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER) {
                $msg = 'msg.you_are_not_subscribed_on_user';
            } else {
                $em = $this->getDoctrine()->getManager();

                $friendRelationship = $userFriend->getRelationship($user->getId());
                $em->remove($friendRelationship);

                $relationship = $user->getRelationship($id);
                $em->remove($relationship);

                $em->flush();

                $msg = 'msg.friendship_request_deleted';
            }
        }
        
        return $this->render('NetworkWebBundle:User:msg.html.twig', [
            'msg' => $msg
        ]);
    }

}
