<?php

namespace Network\StatisticBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Network\StoreBundle\DBAL\RelationshipStatusEnumType;
use Network\StoreBundle\Service\Paginator;

class AchievementRepository extends EntityRepository
{

    /**
     * @param $minDolg
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getUsersWithDolg($minDolg)
    {
//        $qb = $this->getEntityManager()->createQueryBuilder();

        $em         = $this->getEntityManager();
        $connection = $em->getConnection();
        $statement  = $connection->prepare(
            "SELECT TIMESTAMPDIFF(DAY, t.due_date, m.submit_date) AS dolg, ".
            "s.id as student, t.name as task, c.name as course ".
            "FROM parsed_students_marks m INNER JOIN parsed_tasks t ".
            "ON m.task_id = t.id INNER JOIN parsed_students s ON m.student_id = s.id ".
            "INNER JOIN parsed_courses c ON t.course_id = c.id WHERE TIMESTAMPDIFF(DAY, t.due_date, m.submit_date) > :minDolg ORDER BY m.student_id"
        );
        $statement->bindValue('minDolg', $minDolg);
        $statement->execute();
        $results = $statement->fetchAll();

        return $results;
    }

    public function getSumDolg()
    {
        $em         = $this->getEntityManager();
        $connection = $em->getConnection();
        $statement  = $connection->prepare(
            "SELECT SUM( TIMESTAMPDIFF(DAY , t.due_date, m.submit_date ) ) AS dolg, s.id AS student ".
            "FROM parsed_students_marks m ".
            "INNER JOIN parsed_tasks t ON m.task_id = t.id INNER JOIN parsed_students s ON m.student_id = s.id ".
            "INNER JOIN parsed_courses c ON t.course_id = c.id GROUP BY m.student_id"
        );
//        $statement->bindValue('minDolg', $minDolg);
        $statement->execute();
        $results = $statement->fetchAll();

        return $results;
    }

    public function getRatingsWithoutDue()
    {
        $em         = $this->getEntityManager();
        $connection = $em->getConnection();
        $statement  = $connection->prepare(
            "SELECT r.student_id as student, c.name as course, r.result as result, ".
            "r.normal as normal, r.timed as timed FROM parsed_students_ratings r ".
            "INNER JOIN parsed_courses c ON r.course_id = c.id ".
            "WHERE r.normal - r.timed < 5 AND r.result <> ''"
        );
//        $statement->bindValue('minDolg', $minDolg);
        $statement->execute();
        $results = $statement->fetchAll();

        return $results;
    }

    public function getMarksWithValue($value)
    {
        $em         = $this->getEntityManager();
        $connection = $em->getConnection();
        $statement  = $connection->prepare(
            "SELECT m.points as points, t.name as task, c.name as course, s.id AS student ".
            "FROM parsed_students_marks m ".
            "INNER JOIN parsed_tasks t ON m.task_id = t.id INNER JOIN parsed_students s ON m.student_id = s.id ".
            "INNER JOIN parsed_courses c ON t.course_id = c.id ".
            "WHERE points = :value"
        );
        $statement->bindValue('value', $value);
        $statement->execute();
        $results = $statement->fetchAll();

        return $results;
    }

    /**
     * @param $studentId
     * @return array
     */
    public function findForStudent($studentId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('a')
            ->from('NetworkStatisticBundle:Achievement', 'a')
            ->where('a.studentId = :studentId')
            ->setParameter('studentId', $studentId);
         return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param $name
     * @return array
     */
    public function findStudentByName($name)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('s.name, s.id')
            ->from('NetworkStatisticBundle:ParsedStudent', 's')
            ->where('LOWER(s.name) LIKE :name')
            ->setParameters(['name' => '%'.$name.'%']);

        return $qb->getQuery()->getArrayResult();
    }

}
