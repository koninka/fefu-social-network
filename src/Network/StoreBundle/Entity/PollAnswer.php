<?php

namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PollAnswer
 *
 * @ORM\Table(name="poll_answer")
 * @ORM\Entity(repositoryClass="Network\StoreBundle\Repository\PollRepository")
 */
class PollAnswer
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="answer", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $answer;

    /**
     * @ORM\ManyToOne(targetEntity="Poll", inversedBy="answers", cascade={"persist"})
     * @ORM\JoinColumn(name="poll_id", referencedColumnName="id")
     **/
    private $poll;

    /**
     * @ORM\ManyToMany(targetEntity="User", cascade={"persist"})
     * @ORM\JoinTable(name="users_poll_answer",
     *      joinColumns={@ORM\JoinColumn(name="answer_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     *      )
     **/
    private $user;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set answer
     *
     * @param string $answer
     * @return PollAnswer
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * Get answer
     *
     * @return string
     */
    public function getAnswer()
    {
        return $this->answer;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->user = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set poll
     *
     * @param \Network\StoreBundle\Entity\Poll $poll
     * @return PollAnswer
     */
    public function setPoll(\Network\StoreBundle\Entity\Poll $poll = null)
    {
        $this->poll = $poll;

        return $this;
    }

    /**
     * Get poll
     *
     * @return \Network\StoreBundle\Entity\Poll
     */
    public function getPoll()
    {
        return $this->poll;
    }

    /**
     * Add user
     *
     * @param \Network\StoreBundle\Entity\User $user
     * @return PollAnswer
     */
    public function addUser(\Network\StoreBundle\Entity\User $user)
    {
        $this->user[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \Network\StoreBundle\Entity\User $user
     * @return PollAnswer
     */
    public function removeUser(\Network\StoreBundle\Entity\User $user)
    {
        $this->user->removeElement($user);

        return $this;
    }

    /**
     * Get user
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get percent
     *
     * @param integer $sum
     * @return integer
     */
    public function getPercent($sum)
    {
        return $sum > 0
               ? round($this->user->count() / $sum * 100)
               : 0;
    }
}
