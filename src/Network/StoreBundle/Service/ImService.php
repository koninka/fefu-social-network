<?php

namespace Network\StoreBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Network\WebSocketBundle\Message\NotificationMessage;
use Network\WebSocketBundle\Service\ServerManager;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Exception\Exception;
use Network\StoreBundle\Entity\Thread;
use Network\StoreBundle\Entity\Post;
use Network\StoreBundle\Entity\User;
use Network\StoreBundle\Entity\PostFile;
use Network\StoreBundle\DBAL\ThreadEnumType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Translation\Translator;

class ImService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ServerManager
     */
    private $serverManager;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @param EntityManager $em
     * @param ServerManager $serverManager
     * @param Translator $translator
     */
    public function __construct($em, $serverManager, $translator)
    {
        $this->em = $em;
        $this->serverManager = $serverManager;
        $this->translator = $translator;
    }

    public function getThreadByIdAndUserIdOrThrow($threadId, $userId)
    {
        $threadRepo = $this->em->getRepository('NetworkStoreBundle:Thread');
        $thread = $threadRepo->getThreadByIdAndUser($threadId, $userId);
        if ($thread == null) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $thread;
    }

    public function getUsersInThreadByIdAndUserIdOrThrow($threadId, $userId)
    {
        $threadRepo = $this->em->getRepository('NetworkStoreBundle:Thread');
        $users = $threadRepo->getUsersInThread($threadId);
        if (!array_key_exists($userId, $users)) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $users;
    }

    public function isThreadOwner($threadId, $userId) {
        $threadRepo = $this->em->getRepository('NetworkStoreBundle:UserThread');
        $ownerId = $threadRepo->getThreadOwnerId($threadId);

        return $ownerId == $userId;
    }

    public function getInvitedUsersByUserInThread($threadId, $userId)
    {
        $utRepo = $this->em->getRepository('NetworkStoreBundle:UserThread');

        return $utRepo->getInvitedUsersByUserInThread($threadId, $userId);
    }

    public function kickUserFromConference($userId, $conferenceId, $challengerId)
    {
        $manager = $this->em;
        $utRepo = $manager->getRepository('NetworkStoreBundle:UserThread');
        $userThread = $utRepo->getChallengerIfCanBeKickedByUserFromThread($conferenceId, $userId, $challengerId);
        if ($userThread == null) {
            return new JsonResponse(['error' => 'user with given id cannot be kicked from this thread by you']);
        }
        if ($userThread->getThread()->getType() != ThreadEnumType::T_CONFERENCE) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        $manager->remove($userThread);
        $manager->flush();

        $this->serverManager->sendMessage(new NotificationMessage($userId,
                $this->translator->trans('notify.kicked_from_conference', [], 'FOSUserBundle') .
                ' ' . $userThread->getThread()->getTopic(),
            NotificationMessage::TYPE_FAIL));

        return new JsonResponse(['conferenceId' => $conferenceId, 'userId' => $challengerId]);
    }

    public function getConferenceByIdAndUserIdOrThrow($threadId, $userId)
    {
        $thread = $this->getThreadByIdAndUserIdOrThrow($threadId, $userId);
        if ($thread->getType() != ThreadEnumType::T_CONFERENCE) {
            throw new Exception('Invalid conference Id');
        }

        return $thread;
    }

    public function createPost(User $user, Thread $thread, $text, $filesId = NULL)
    {
        $oldTimeZone = date_default_timezone_get();
        date_default_timezone_set("UTC");

        $post = new Post();
        $post->setText($text)
            ->setTs(new \DateTime('now'))
            ->setUser($user)
            ->setThread($thread);

        if(isset($filesId) && count($filesId['postFile']) > 0) {
            foreach ($filesId['postFile'] as $fileId) {
                $file = $this->em->getRepository('NetworkStoreBundle:PostFile')->find($fileId);
                $file->setPost($post);
                $post->addFile($file);
                $this->em->persist($file);
            }
        }

        $thread->incUnreadPosts($user);
        $this->em->persist($post);
        $this->em->flush();

        date_default_timezone_set($oldTimeZone);

        return $post;
    }

    public function createDialogOrConference(User $user, ArrayCollection $recipientUsers, $topic)
    {
        $recipientsCount = $recipientUsers->count();
        if ($recipientUsers->count() == 1) {
            $recipientUser = $recipientUsers[0];
            $thread = $this->createDialog($user, $recipientUser);
        } else {
            $thread = $this->createConference($user, $recipientUsers, $recipientsCount, $topic);
        }

        return $thread;
    }

    private function makeTopicFromUsers(ArrayCollection $recipientUsers, $recipientsCount)
    {
        $topic = '';
        foreach ($recipientUsers as $k => $recipientUser) {
            $topic .= $recipientUser->getFirstName();
            if ($k + 1 < $recipientsCount) {
                $topic .= ', ';
            }
        }

        return $topic;
    }
    private function createConference(User $user, ArrayCollection $recipientUsers, $recipientsCount, $topic)
    {
        if (trim($topic) == '') {
            $topic = $this->makeTopicFromUsers($recipientUsers, $recipientsCount);
        }
        $thread = new Thread();
        $thread
            ->setTopic($topic)
            ->setType(ThreadEnumType::T_CONFERENCE);
        $this->persistAndFlush($thread);
        $userId = $user->getId();
        $thread->addUser($user, $userId);
        foreach ($recipientUsers as $recipientUser) {
            $thread->addUser($recipientUser, $userId);
            $this->serverManager->sendMessage(new NotificationMessage($recipientUser->getId(),
                    $this->translator->trans('notify.invited_to_conference', [], 'FOSUserBundle') .
                    ' ' . $topic,
                    NotificationMessage::TYPE_SUCCESS));
        }

        $this->persistAndFlush($thread);

        return $thread;
    }

    private function createDialog(User $user, User $recipientUser)
    {
        $thread = $this->em
            ->getRepository('NetworkStoreBundle:Thread')
            ->findByUsers($user->getId(), $recipientUser->getId());
        if (!$thread or count($thread) == 0) {
            // there's no 1x1 thread between this pair of users
            // so we're creating a new one
            $thread = new Thread();
            $thread->setTopic('no topic');
            $this->persistAndFlush($thread); //because of foreign key error
            $thread->addUser($user, $user->getId())->addUser($recipientUser, $user->getId());
            $this->persistAndFlush($thread);

        } elseif (count($thread) > 1) {
            throw new Exception('SERVER ERROR: 2 dialogs between 2 persons');
        } else {
            $thread = $thread[0];
        }

        return $thread;
    }

    public function checkAccessForThread($threadId, $userId)
    {
        $threadRepo = $this->em->getRepository('NetworkStoreBundle:Thread');
        if (!$threadRepo->checkPermission($threadId, $userId)) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
    }

    private function persistAndFlush($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();
    }

    public function readPosts($userId, $threadId, $count)
    {
        if ($threadId == null) {
            return new JsonResponse(['error' => 'Invalid thread Id']);
        }
        $userThreadRep = $this->em->getRepository('NetworkStoreBundle:UserThread');
        $userThread = $userThreadRep->findByUserAndThread($userId, $threadId);
        if ($userThread == null) {
            return new JsonResponse(['error' => 'thread not found']);
        }
        $userThread->decUnreadPosts($count);
        $this->persistAndFlush($userThread);

        return new JsonResponse(['count' => $count]);
    }

    public function sendMessage($msg) {
        $this->serverManager->sendMessage($msg);
    }

    function normalizePost(Post $post)
    {
        $postFiles = [];

        foreach ($post->getFiles()->toArray() as $file) {
            $postFiles[] = [
                'id'   => $file->getId(),
                'name' => $file->getName(),
                'hash' => $file->getHash()
            ];
        }

        $postResult = [
            'id'        => $post->getId(),
            'ts'        => $post->getTs(),
            'text'      => $post->getText(),
            'author'    => $post->getUser()->getFirstName() . ' ' . $post->getUser()->getLastName(),
            'postFiles' => $postFiles,
            'userId'    => $post->getUser()->getId()
        ];

        return $postResult;
    }

    public function getMessageById($id)
    {
        $msg = $this->em
            ->getRepository('NetworkStoreBundle:Post')
            ->find($id);

        return $msg;
    }
}
