<?php

namespace Network\StatisticBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Network\StatisticBundle\Entity\ParsedTask;
use Network\StatisticBundle\Entity\ParsedCourse;
use Network\StatisticBundle\Entity\ParsedCrit;
use Network\StatisticBundle\Entity\ParsedStudentMark;
use Network\StatisticBundle\Entity\ParsedStudentRating;
use Network\StatisticBundle\Entity\ParsedStudent;

function getSetterName($jsonName)
{
    return 'set' . preg_replace_callback(
        '/(_\w)/i',
        function ($match) {
            return strtoupper(substr($match[0], 1));
        },
        '_' . $jsonName
    );
}

function tryToSetValue($entity, $setterFunc, $value)
{
    if (method_exists($entity, $setterFunc)) {
        $entity->$setterFunc($value);

        return true;
    }

    return false;
}

function echoString($str)
{
    echo '<p>' . $str . '<p>';
}

class ParseController extends Controller
{

    public function homeAction()
    {
        return $this->render('NetworkStatisticBundle:Parse:home.html.twig');
    }

    public function parseAction($startYear, $endYear)
    {
        set_time_limit(0);

//        $startYear = 2011;
//        $endYear = 2015;

        $studentsArr = [];
        $tasksArr = [];
        $critsArr = [];
        $marksArr = [];
        $coursesArr = [];

        //task
        $coursesKey  = 'courses';
        $tasksKey    = 'tasks';
        $taskDateKey = 'due_date';

        //course
        $totalKey = 'total';

        //crit
        $critsKey        = 'crits';
        $critPassableKey = 'passable';

        //students
        $studentsKey      = 'students';
        $marksKey         = 'marks';
        $markDateKey      = 'submit_date';
        $markDepPointsKey = 'dep_points';
        $markPointsKey    = 'points';
        $ratingKey        = 'rating';
        $ratingOkKey      = 'ok';

        $dateFormat = 'd.m.Y';
        $idKey      = 'id';

        $em = $this->getDoctrine()->getManager();

        //clear tables
//        $em->createQuery('DELETE FROM NetworkStatisticBundle:ParsedTask')->execute();
//        $em->createQuery('DELETE FROM NetworkStatisticBundle:ParsedCourse')->execute();
//        $em->createQuery('DELETE FROM NetworkStatisticBundle:ParsedCrit')->execute();
//        $em->createQuery('DELETE FROM NetworkStatisticBundle:ParsedStudentMark')->execute();
//        $em->createQuery('DELETE FROM NetworkStatisticBundle:ParsedStudentRating')->execute();
//        $em->createQuery('DELETE FROM NetworkStatisticBundle:ParsedStudent')->execute();

        //got from imcs and fixed
        $groupCourses =
            [
                "11034" => [10945],
                "12126" => [12422, 12273, 14474, 1766, 16511, 19139, 19140, 19166],
                "12215" => [16634],
                "14748" => [14747],
                "14902" => [14747],
                "15798" => [14747],
                "16182" => [16286, 16287, 19064, 19462],
                "1765"  => [3753, 6411, 14376, 8144, 22082, 12125, 24206, 3173],
                "1768"  => [3476, 1766, 14471, 8560, 1851],
                "1861"  => [3753, 3173],
                "2107"  => [19193, 22073, 33321, 3171, 1767], //added 1767
                "22194" => [24206, 22082],
                "24170" => [19035],
                "2443"  => [16635, 9755, 4206, 16633, 31211, 31313, 2799],
                "2536"  => [1767, 30593, 19491, 3171],
                "27579" => [30524, 8560, 1851, 2439, 12125], //added 2125
                "27580" => [8560, 1851, 30524],
                "27879" => [27901, 30313, 27975, 15797],
                "28664" => [8560, 1851, 30524],
                "28665" => [30525, 31057, 8560, 1851, 2439, 30524],
                "2882"  => [10546, 3476, 8843, 1766],
                "2888"  => [22428, 2885, 2884, 25274, 19035, 3171],
                "3240"  => [3457],
                "34007" => [2799],
                "34035" => [2799],
                "35036" => [35057],
                "35072" => [30524],
                "3554"  => [3732, 6844],
                "3555"  => [3732, 6844],
                "4466"  => [4471, 12004],
                "6240"  => [8373, 14642, 6243, 6244, 24088, 14641],
                "6480"  => [9755, 4206, 31211, 31313]
            ];

        //turn off Buffering
        while (@ob_end_flush());
        ob_implicit_flush(true);

        //construct urls
        $urls = [];
        for ($year = $startYear; $year <= $endYear; $year++) {
            foreach ($groupCourses as $group => $courses) {
                foreach ($courses as $course) {
                    $urls[] = "http://imcs.dvfu.ru/works/marks?course=$course;group=$group;year=$year;order_by=n;json=1";
                }
            }
        }

        //parse it!
        foreach ($urls as $url) {
            $result = json_decode(file_get_contents($url), true);
            echoString('parsing: ' . $url);

            if (!array_key_exists($coursesKey, $result)) {
                //bad request
                continue;
            }
            //parse Courses
            foreach ($result[$coursesKey] as $course) {
                if (!array_key_exists($totalKey, $course)) {
                    //empty course
                    continue;
                }
                //parse Tasks
                foreach ($course[$tasksKey] as $task) {
                    if (!array_key_exists($task[$idKey], $tasksArr)) {
                        $tasksArr[$task[$idKey]] = $task;
                    }
                }
                unset($course[$tasksKey]);
                //parse Crits
                foreach ($course[$critsKey] as $crit) {
                    if (!array_key_exists($crit[$idKey], $critsArr)) {
                        $critsArr[$crit[$idKey]] = $crit;
                    }
                }
                unset($course[$critsKey]);
                //parse Course
                if (!array_key_exists($course[$idKey], $coursesArr)) {
                    $coursesArr[$course[$idKey]] = $course;
                }
            }

            //parse Students Data
            foreach ($result[$studentsKey] as $student) {
                //dont allow to duplicate students
                if (!array_key_exists($student[$idKey], $studentsArr)) {
                    $studentsArr[$student[$idKey]] = $student;
                }
                if (!array_key_exists($marksKey, $student)) {
                    //student with no marks
                    continue;
                }
                //parse marks
                foreach ($student[$marksKey] as $marks) {
                    foreach ($marks as $mark) {
                        if (!array_key_exists($mark[$idKey], $marksArr)) {
                            $marksArr[$mark[$idKey]] = $mark;
                        }
                    }
                }
                if (!array_key_exists($ratingKey, $student)) {
                    //no ratings
                    continue;
                }
                $ratingObj = new ParsedStudentRating;
                $ratingObj->setStudentId($student[$idKey]);
                //one student = one rating
                foreach ($student[$ratingKey] as $courseId => $rating) {
                    $ratingObj->setCourseId($courseId);
                    if (!(array_key_exists($ratingOkKey, $rating)) || is_null($rating[$ratingOkKey])) {
                        //klenin's error fix
                        $rating[$ratingOkKey] = 1;
                    }
                    foreach ($rating as $key => $data) {
                        tryToSetValue($ratingObj, getSetterName($key), $data);
                    }
                }
                $em->persist($ratingObj);
            }
        }

        echoString('Inserting ratings...');
        $em->flush();

        echoString('Inserting students...');

        //insert students
        foreach ($studentsArr as $student) {
            $studentObj = new ParsedStudent($student[$idKey]);
            unset($student[$idKey]);
            unset($student[$ratingKey]);
            unset($student[$marksKey]);
            foreach ($student as $key => $data) {
                tryToSetValue($studentObj, getSetterName($key), $data);
            }
            $em->persist($studentObj);
        }
        $em->flush();

        echoString('Inserting tasks...');

        //insert tasks
        foreach ($tasksArr as $task) {
            $taskObj = new ParsedTask($task[$idKey]);
            unset($task[$idKey]);
            $task[$taskDateKey] = date_create_from_format($dateFormat, $task[$taskDateKey]);
            foreach ($task as $key => $data) {
                tryToSetValue($taskObj, getSetterName($key), $data);
            }
            $em->persist($taskObj);
        }
        $em->flush();

        echoString('Inserting marks...');

        $marksArr = array_slice($marksArr, 8889);
        //insert marks
        $f = 0;
        foreach ($marksArr as $mark) {
            $markObj = new ParsedStudentMark($mark[$idKey]);
            unset($mark[$idKey]);
            $mark[$markDateKey] = date_create_from_format($dateFormat, $mark[$markDateKey]);
            if (!array_key_exists($markDepPointsKey, $mark)) {
                $mark[$markDepPointsKey] = $mark[$markPointsKey];
            }
            foreach ($mark as $key => $data) {
                tryToSetValue($markObj, getSetterName($key), $data);
            }
            $em->persist($markObj);
            $f++;
            if ($f % 100 == 0) {
                $em->flush();
                $f = 0;
            }
        }
        $em->flush();

        echoString('Inserting courses...');

        //insert courses
        $f = 0;
        foreach ($coursesArr as $course) {
            $courseObj = new ParsedCourse($course[$idKey]);
            unset($course[$idKey]);
            foreach ($course as $key => $data) {
                tryToSetValue($courseObj, getSetterName($key), $data);
            }
            foreach ($course[$totalKey] as $key => $data) {
                tryToSetValue($courseObj, getSetterName($key), $data);
            }
            $em->persist($courseObj);
            $f++;
            if ($f % 100 == 0) {
                $em->flush();
                $f = 0;
            }
        }
        $em->flush();

        echoString('Inserting crits...');

        //insert crits
        $f = 0;
        foreach ($critsArr as $crit) {
            if (!array_key_exists($crit[$idKey], $critsArr)) {
                $critsArr[$crit[$idKey]] = $crit;
            }
            $critObj = new ParsedCrit($crit[$idKey]);
            unset($crit[$idKey]);
            if (is_null($crit[$critPassableKey])) {
                //klenin's error fix
                $crit[$critPassableKey] = 1;
            }
            foreach ($crit as $key => $data) {
                tryToSetValue($critObj, getSetterName($key), $data);
            }
            $em->persist($critObj);
            $f++;
            if ($f % 100 == 0) {
                $em->flush();
                $f = 0;
            }
        }
        $em->flush();


        return $this->render('NetworkStatisticBundle:Parse:parse.html.twig');
    }
}
