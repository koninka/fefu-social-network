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
        $rep = $this->em->getRepository('NetworkStatisticBundle:Achievement');
        $this->em->createQuery('DELETE FROM NetworkStatisticBundle:Achievement')->execute();

        $dolgs = $rep->getUsersWithDolg(365);
        foreach ($dolgs as $dolg) {
            $achievement = new Achievement();
            $achievement->setStudentId($dolg['student']);
            $achievement->setName('Годовой долг');
            $achievement->setDescription('Задание "' . $dolg['task'] . '" по курсу "' . $dolg['course'] . '" сдано позже чем через год');
            $achievement->setCountable(false);
            $achievement->setCount(0);
            $this->em->persist($achievement);
        }
        $this->em->flush();
        $this->em->clear();
        $dolgs = $rep->getSumDolg();
        foreach ($dolgs as $dolg) {
            $achievement = new Achievement();
            $achievement->setStudentId($dolg['student']);
            $achievement->setName('Общий долг');
            $achievement->setDescription('Общий долг по всем сданным заданиям составляет ' . $dolg['dolg'] . ' дней');
            $achievement->setCountable(false);
            $achievement->setCount(0);
            $this->em->persist($achievement);
        }
        $this->em->flush();
        $this->em->clear();
        $dolgs = $rep->getRatingsWithoutDue();
        foreach ($dolgs as $dolg) {
            $achievement = new Achievement();
            $achievement->setStudentId($dolg['student']);
            $achievement->setName('Курс без опозданий');
            $achievement->setDescription('Курс "' . $dolg['course'] . '" сдан с рейтингом ' . $dolg['normal'] . '/' . $dolg['timed'] . ' на "' . $dolg['result'] . '"');
            $achievement->setCountable(false);
            $achievement->setCount(0);
            $this->em->persist($achievement);
        }
        $this->em->flush();
        $this->em->clear();
        $dolgs = $rep->getMarksWithValue(1);
        foreach ($dolgs as $dolg) {
            $achievement = new Achievement();
            $achievement->setStudentId($dolg['student']);
            $achievement->setName('На 1 балл');
            $achievement->setDescription('Задание "' . $dolg['task'] . '" по курсу ' . $dolg['course'] . ' сдано на "' . $dolg['points'] . '" балл');
            $achievement->setCountable(false);
            $achievement->setCount(0);
            $this->em->persist($achievement);
        }
        $this->em->flush();
        $this->em->clear();
        $dolgs = $rep->getMarksWithValue(10);
        foreach ($dolgs as $dolg) {
            $achievement = new Achievement();
            $achievement->setStudentId($dolg['student']);
            $achievement->setName('На 10 баллов');
            $achievement->setDescription('Задание "' . $dolg['task'] . '" по курсу ' . $dolg['course'] . ' сдано на "' . $dolg['points'] . '" баллов');
            $achievement->setCountable(false);
            $achievement->setCount(0);
            $this->em->persist($achievement);
        }
        $this->em->flush();
    }


}
