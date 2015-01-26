<?php

namespace Network\StatisticBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Network\StoreBundle\DBAL\RelationshipStatusEnumType;
use Network\StoreBundle\Service\Paginator;

class AchievementRepository extends EntityRepository
{

    public function getUsersWithDolg($minDolg)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

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

}
