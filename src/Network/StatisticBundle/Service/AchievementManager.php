<?php
namespace Network\StatisticBundle\Service;

use Doctrine\ORM\EntityManager;
use Network\StatisticBundle\Entity\Achievement;
use Network\StoreBundle\DBAL\RelationshipStatusEnumType;
use Network\StoreBundle\Entity\Relationship;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class AchievementManager extends Controller
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function calcAchievements()
    {
        $dolgs = $this->em->getRepository('NetworkStatisticBundle:Achievement')->getUsersWithDolg(365);
        foreach ($dolgs as $dolg) {
            $achievement = new Achievement();
            $achievement->setStudentId($dolg['student']);
            $achievement->setName('Годовой долг');
            $achievement->setDescription('Годовой долг по курсу "' . $dolg['course'] . '" по заданию "' . $dolg['task'] . '"');
            $achievement->setCountable(false);
            $achievement->setCount(0);
            $this->em->persist($achievement);
        }
        $this->em->flush();

    }


}
