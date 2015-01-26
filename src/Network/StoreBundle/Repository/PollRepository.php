<?php

namespace Network\StoreBundle\Repository;


use Doctrine\ORM\EntityRepository;
use Network\StoreBundle\Entity\Poll;
use Network\UserBundle\Form\Type\PollType;

/**
* UserThreadRepository
*
*/
class PollRepository extends EntityRepository
{

    /**
     * @param Network\StoreBundle\Entity\Poll $poll
     * @param integer $userId
     * @return bool
     */
    public function isUserAnswer($poll, $userId)
    {
        $ans = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('m')
            ->from('NetworkStoreBundle:PollAnswer', 'm')
            ->join('m.user', 'u')
            ->where('u.id = :userId' )
            ->andWhere('m.poll = :pollId')
            ->setParameters(['userId'=> $userId, 'pollId'=> $poll->getId()])
            ->getQuery()
            ->getResult();
            
        return !empty($ans);
    }

    /**
     * @param Network\StoreBundle\Entity\Poll $poll
     * @return integer
     */
    public function countAnswer($poll)
    {
        $ans = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('count(m)')
            ->from('NetworkStoreBundle:PollAnswer', 'm')
            ->join('m.user', 'u')
            ->where('m.poll = :pollId')
            ->setParameters(['pollId'=> $poll->getId()])
            ->getQuery()
            ->getSingleScalarResult();
            
        return $ans;
    }
    
    /**
     * @param Network\StoreBundle\Entity\Poll $poll
     * @return bool
     */
    public function hasVoted($poll)
    {
        $ans = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('m')
            ->from('NetworkStoreBundle:PollAnswer', 'm')
            ->join('m.user', 'u')
            ->andWhere('m.poll = :pollId')
            ->setParameters(['pollId'=> $poll->getId()])
            ->getQuery()
            ->getResult();
            
        return !empty($ans);
    }
    
    /**
     * @param integer $postId
     * @return bool
     */
    public function getPoll($postId)
    {
        $ans = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('m')
            ->from('NetworkStoreBundle:Poll', 'm')
            ->where('m.post = :postId' )
            ->setParameters(['postId'=> $postId])
            ->getQuery()
            ->getResult();
            
        return $ans;
    }
}
