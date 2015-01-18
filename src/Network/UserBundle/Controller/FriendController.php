<?php

namespace Network\UserBundle\Controller;

use Network\StoreBundle\DBAL\RelationshipStatusEnumType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Network\StoreBundle\Entity\Relationship;

class FriendController extends Controller
{
    use ProfileTrait;

    const ACTION_SEND = 'action_send';
    const ACTION_ACCEPT = 'action_accept';
    const ACTION_DECLINE = 'action_decline';
    const ACTION_DELETE = 'action_delete';
    const ACTION_DELETE_SUBSCRIPTION = 'action_delete_subscription';

    private function handleFriendshipRequest($id, $action)
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
            $rels = $this->getDoctrine()->getRepository('NetworkStoreBundle:Relationship');
            $relationship = $rels->getRelationshipForUser($user->getId(), $userFriend->getId());
            $friendRelationship = $rels->getRelationshipForUser($userFriend->getId(), $user->getId());
            switch ($action) {
                case self::ACTION_SEND:
                    if ($relationship->getStatus() === RelationshipStatusEnumType::FS_ACCEPTED) {
                        $msg = 'msg.is_already_friend';
                    } elseif ($relationship->getStatus() === RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME) {
                        $msg = 'msg.is_already_subscribed';
                    } elseif ($relationship->getStatus() === RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER) {
                        $friendRelationship->setStatus(RelationshipStatusEnumType::FS_ACCEPTED);
                        $em->persist($friendRelationship);

                        $relationship->setStatus(RelationshipStatusEnumType::FS_ACCEPTED);
                        $em->persist($relationship);

                        $em->flush();

                        $msg = 'msg.friendship_accepted';
                    } elseif ($relationship->getStatus() != RelationshipStatusEnumType::FS_NONE) {
                        $msg = $relationship->getStatus().'msg.unknown_error';
                    } else {
                        $userRelationship = new Relationship();
                        $userRelationship->setUser($user)
                                         ->setPartner($userFriend)
                                         ->setStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME);
                        $em->persist($userRelationship);

                        $newFriendRelationship = new Relationship();
                        $newFriendRelationship->setUser($userFriend)
                                           ->setPartner($user)
                                           ->setStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER);
                        $em->persist($newFriendRelationship);

                        $em->flush();

                        $msg = 'msg.friendship_request_sent';
                    }
                    break;
                case self::ACTION_ACCEPT:
                    if ($friendRelationship->getStatus() === RelationshipStatusEnumType::FS_ACCEPTED) {
                        $msg = 'msg.is_already_friend';
                    } elseif ($friendRelationship->getStatus() != RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME) {
                        $msg = 'msg.user_does_not_sent_friendship_request';
                    } else {
                        $friendRelationship->setStatus(RelationshipStatusEnumType::FS_ACCEPTED);
                        $em->persist($friendRelationship);

                        $relationship->setStatus(RelationshipStatusEnumType::FS_ACCEPTED);
                        $em->persist($relationship);

                        $em->flush();

                        $msg = 'msg.friendship_accepted';
                    }
                    break;
                case self::ACTION_DECLINE:
                    if ($friendRelationship->getStatus() === RelationshipStatusEnumType::FS_ACCEPTED) {
                        $msg = 'msg.is_already_friend';
                    } elseif ($friendRelationship->getStatus() != RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME) {
                        $msg = 'msg.user_does_not_sent_friendship_request';
                    } else {
                        $friendRelationship->setStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME);
                        $em->persist($friendRelationship);

                        $relationship->setStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER)
                                     ->setHidden(true);
                        $em->persist($relationship);

                        $em->flush();

                        $msg = 'msg.friendship_request_declined';
                    }
                    break;
                case self::ACTION_DELETE:
                    if ($friendRelationship->getStatus() != RelationshipStatusEnumType::FS_ACCEPTED) {
                        $msg = 'msg.user_is_not_friend';
                    } else {
                        $friendRelationship->setStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME);
                        $em->persist($friendRelationship);

                        $relationship->setStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER)
                                     ->setHidden(true);
                        $em->persist($relationship);

                        $em->flush();

                        $msg = 'msg.friendship_deleted';
                    }
                    break;
                case self::ACTION_DELETE_SUBSCRIPTION:
                    if ($friendRelationship->getStatus() != RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER) {
                        $msg = 'msg.you_are_not_subscribed_on_user';
                    } else {
                        $em->remove($friendRelationship);

                        $em->remove($relationship);

                        $em->flush();

                        $msg = 'msg.friendship_request_deleted';
                    }
                    break;
            }
        }

        return $this->render('NetworkWebBundle:User:msg.html.twig', [
                'msg' => $msg
        ]);
    }

    public function sendFriendshipRequestAction($id)
    {
        return $this->handleFriendshipRequest($id, self::ACTION_SEND);
    }

    public function acceptFriendshipRequestAction($id)
    {
        return $this->handleFriendshipRequest($id, self::ACTION_ACCEPT);
    }

    public function declineFriendshipAction($id)
    {
        return $this->handleFriendshipRequest($id, self::ACTION_DECLINE);
    }

    public function deleteFriendshipAction($id)
    {
        return $this->handleFriendshipRequest($id, self::ACTION_DELETE);
    }

    public function deleteFriendshipSubscriptionAction($id)
    {
        return $this->handleFriendshipRequest($id, self::ACTION_DELETE_SUBSCRIPTION);
    }

}
