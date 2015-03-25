<?php
namespace Network\StoreBundle\Service;

use Doctrine\ORM\EntityManager;
use Network\StoreBundle\DBAL\RelationshipStatusEnumType;
use Network\StoreBundle\Entity\Relationship;
use Network\WebSocketBundle\Message\NotificationMessage;
use Network\WebSocketBundle\Service\ServerManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use Network\WebSocketBundle\Message\Message;
use Symfony\Component\Translation\Translator;

class RelationshipManager extends Controller
{
    protected $em;
    protected $user;
    protected $serverManager;
    protected $translator;

    public function __construct(EntityManager $em, SecurityContext $securityContext,
        ServerManager $serverManager, Translator $translator)
    {
        $this->em   = $em;
        $this->user = $securityContext->getToken()->getUser();
        $this->serverManager = $serverManager;
        $this->translator = $translator;
    }

    /**
     * @param $parnterId
     * @return array
     * @throws \Exception
     */
    private function getPartnerAndRelationships($parnterId)
    {
        $partner = $this->em->getRepository('NetworkStoreBundle:User')->find($parnterId);
        if (!$partner) {
            throw new \Exception("User not found");
        }
        if (!$this->user) {
            throw new \Exception("Not authorized");
        }
        if ($this->user->getId() == $parnterId) {
            throw new \Exception("Same user");
        }
        $rels = $this->em->getRepository('NetworkStoreBundle:Relationship');

        return [
            $partner,
            $rels,
            $rels->getRelationshipForUser($this->user->getId(), $partner->getId()),
            $rels->getRelationshipForUser($partner->getId(), $this->user->getId())
        ];
    }

    private function sendMessage($userId, $transMsg, $type) {
        $this->serverManager->sendMessage(new NotificationMessage($userId,
                $this->user->getFirstName() . ' ' . $this->user->getLastName() . ' ' .
                $this->translator->trans($transMsg, [], 'FOSUserBundle'),
                $type));
    }

    /**
     * @param $id
     * @return string
     * @throws \Exception
     */
    public function sendFriendshipRequest($id)
    {
        list ($partner, $rels, $relationship, $friendRelationship) = $this->getPartnerAndRelationships($id);
        if ($relationship->getStatus() === RelationshipStatusEnumType::FS_ACCEPTED) {
            throw new \Exception("User is already a friend");
        }
        if ($relationship->getStatus() === RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME) {
            throw new \Exception("You are already subscribed on this user");
        }
        if ($relationship->getStatus() === RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER) {
            $friendRelationship->setStatus(RelationshipStatusEnumType::FS_ACCEPTED);
            $this->em->persist($friendRelationship);

            $relationship->setStatus(RelationshipStatusEnumType::FS_ACCEPTED);
            $this->em->persist($relationship);

            $this->em->flush();

            $this->sendMessage($id, 'notify.accept_friendship_request', NotificationMessage::TYPE_SUCCESS);

            return 'friendship_accepted';
        }

        if ($relationship->getStatus() != RelationshipStatusEnumType::FS_NONE) {
            throw new \Exception("Unknown Error");
        }
        $userRelationship = new Relationship();
        $userRelationship->setUser($this->user)
            ->setPartner($partner)
            ->setStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME);
        $this->em->persist($userRelationship);

        $newFriendRelationship = new Relationship();
        $newFriendRelationship->setUser($partner)
            ->setPartner($this->user)
            ->setStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER);
        $this->em->persist($newFriendRelationship);

        $this->em->flush();

        $this->sendMessage($id, 'notify.friendship_request_received', NotificationMessage::TYPE_SUCCESS);

        return 'friendship_request_sent';
    }

    /**
     * @param $id
     * @return string
     * @throws \Exception
     */
    public function acceptFriendshipRequest($id)
    {
        list ($partner, $rels, $relationship, $friendRelationship) = $this->getPartnerAndRelationships($id);
        if ($friendRelationship->getStatus() === RelationshipStatusEnumType::FS_ACCEPTED) {
            throw new \Exception("User is already a friend");
        }
        if ($friendRelationship->getStatus() != RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME) {
            throw new \Exception("User doesnt send a friendship request");
        }
        $friendRelationship->setStatus(RelationshipStatusEnumType::FS_ACCEPTED);
        $this->em->persist($friendRelationship);

        $relationship->setStatus(RelationshipStatusEnumType::FS_ACCEPTED);
        $this->em->persist($relationship);

        $this->em->flush();

        $this->sendMessage($id, 'notify.accept_friendship_request', NotificationMessage::TYPE_SUCCESS);
    }

    /**
     * @param $id
     * @return string
     * @throws \Exception
     */
    public function declineFriendship($id)
    {
        list ($partner, $rels, $relationship, $friendRelationship) = $this->getPartnerAndRelationships($id);
        if ($friendRelationship->getStatus() === RelationshipStatusEnumType::FS_ACCEPTED) {
            throw new \Exception("User is already a friend");
        }
        if ($friendRelationship->getStatus() != RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME) {
            throw new \Exception("User doesnt send a friendship request");
        }
        $friendRelationship->setStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME);
        $this->em->persist($friendRelationship);

        $relationship->setStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER)
            ->setHidden(true);
        $this->em->persist($relationship);

        $this->em->flush();

        $this->sendMessage($id, 'notify.decline_friendship_request', NotificationMessage::TYPE_FAIL);

        return 'friendship_request_declined';
    }

    /**
     * @param $id
     * @return string
     * @throws \Exception
     */
    public function deleteFriendship($id)
    {
        list ($partner, $rels, $relationship, $friendRelationship) = $this->getPartnerAndRelationships($id);
        if ($friendRelationship->getStatus() != RelationshipStatusEnumType::FS_ACCEPTED) {
            throw new \Exception("User is not a friend");
        }
        $friendRelationship->setStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME);
        $this->em->persist($friendRelationship);

        $relationship->setStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER)
            ->setHidden(true);
        $this->em->persist($relationship);

        $this->em->flush();

        $this->sendMessage($id, 'notify.remove_from_friends', NotificationMessage::TYPE_FAIL);

        return 'friendship_deleted';
    }

    /**
     * @param $id
     * @return string
     * @throws \Exception
     */
    public function deleteFriendshipSubscription($id)
    {
        list ($partner, $rels, $relationship, $friendRelationship) = $this->getPartnerAndRelationships($id);
        if ($friendRelationship->getStatus() != RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER) {
            throw new \Exception("You are not subscribed on this user");
        }
        $this->em->remove($friendRelationship);

        $this->em->remove($relationship);

        $this->em->flush();

        $this->sendMessage($id, 'notify.unsubscribe', NotificationMessage::TYPE_FAIL);

        return 'friendship_request_deleted';
    }
}
