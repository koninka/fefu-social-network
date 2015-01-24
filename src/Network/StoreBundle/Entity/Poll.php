<?php

namespace Network\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Poll
 *
 * @ORM\Table(name="polls")
 * @ORM\Entity
 */
class Poll
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
     * @var text
     *
     * @ORM\Column(name="question", type="text")
     * @Assert\NotBlank()
     */
    private $question;

    /**
     * @ORM\OneToMany(targetEntity="PollAnswer", mappedBy="poll", cascade={"persist"}, orphanRemoval=true)
     * @Assert\NotBlank()
     **/
    private $answers;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
     */
    private $owner;

    /**
     * var boolean
     *
     * @ORM\Column(name="is_anonymously", type="boolean")
     */
    private $isAnonymously;


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
     * Set question
     *
     * @param string $question
     * @return Poll
     */
    public function setQuestion($question)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question
     *
     * @return string
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set owner
     *
     * @param \Network\StoreBundle\Entity\User $owner
     * @return Poll
     */
    public function setOwner(\Network\StoreBundle\Entity\User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return \Network\StoreBundle\Entity\User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->answers = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add answers
     *
     * @param \Network\StoreBundle\Entity\PollAnswer $answers
     * @return Poll
     */
    public function addAnswer(\Network\StoreBundle\Entity\PollAnswer $answers)
    {
        $this->answers[] = $answers;
        $answers->setPoll($this);

        return $this;
    }

    /**
     * Remove answers
     *
     * @param \Network\StoreBundle\Entity\PollAnswer $answers
     * @return Poll
     */
    public function removeAnswer(\Network\StoreBundle\Entity\PollAnswer $answers)
    {
        $this->answers->removeElement($answers);
        $answers->setPoll(null);

         return $this;
    }

    /**
     * Get answers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAnswers()
    {
        foreach ($this->answers as $answers) {
            $answers->setPoll($this);
        }

        return $this->answers;
    }

    /**
     * Set answers
     *
     * @param \Doctrine\Common\Collections\Collection  $answers
     * @return Poll
     */
    public function setAnswers(\Doctrine\Common\Collections\Collection  $answers)
    {
        $this->answers = $answers;
        foreach ($answers as $ans) {
            $ans->setPoll($this);
        }

        return $this;
    }

    /**
     * @Assert\True(message="You must specify at least one response")
     * @return bool
     */
    public function isCheckResponseOptions()
    {
        return $this->answers->count() > 0;
    }

    /**
     * Set isAnonymously
     *
     * @param boolean $isAnonymously
     * @return Poll
     */
    public function setIsAnonymously($isAnonymously)
    {
        $this->isAnonymously = $isAnonymously;

        return $this;
    }

    /**
     * Get isAnonymously
     *
     * @return boolean
     */
    public function getIsAnonymously()
    {
        return $this->isAnonymously;
    }
}
