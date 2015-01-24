<?php

namespace Network\StoreBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Exception\Exception;
use Network\StoreBundle\Entity\Thread;
use Network\StoreBundle\Entity\Post;
use Network\StoreBundle\Entity\User;
use Network\StoreBundle\DBAL\ThreadEnumType;
use Symfony\Component\HttpFoundation\JsonResponse;

class ImService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct($em)
    {
        $this->em = $em;
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

    public function getConferenceByIdAndUserIdOrThrow($threadId, $userId)
    {
        $thread = $this->getThreadByIdAndUserIdOrThrow($threadId, $userId);
        if ($thread->getType() != ThreadEnumType::T_CONFERENCE) {
            throw new Exception('Invalid conference Id');
        }

        return $thread;
    }

    public function createPost(User $user, Thread $thread, $text)
    {
        $oldTimeZone = date_default_timezone_get();
        date_default_timezone_set("UTC");

        $post = new Post();
        $post->setText($text)
            ->setTs(new \DateTime('now'))
            ->setUser($user)
            ->setThread($thread);
        $thread->incUnreadPosts($user);

        $this->persistAndFlush($post);

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
        foreach ($recipientUsers as $recipientUser) {
            $thread->addUser($recipientUser);
        }
        $thread->addUser($user);
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
            $thread->addUser($user)->addUser($recipientUser);
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

}
