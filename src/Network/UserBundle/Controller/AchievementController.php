<?php

namespace Network\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class AchievementController extends Controller
{

    public function showAchievementsAction($id)
    {
        $user = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($id);
        if (empty($user)) {
            return $this->redirect($this->generateUrl('mainpage'));
        }
        $isCurUser = false;
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            $curUser = $this->getUser();
            $isCurUser = ($curUser->getId() === $user->getId());
        }
        $showSearch = false;
        $achievements = [];
        if (is_null($user->getParsedStudent())) {
            $showSearch = true;
        } else {
            $achievements = $this->getDoctrine()->getRepository('NetworkStatisticBundle:Achievement')
                                                ->findForStudent($user->getParsedStudent()->getId());
        }

        return $this->render('NetworkUserBundle:Profile:achievements.html.twig', [
                'is_cur_user' => $isCurUser,
                'show_search' => $showSearch,
                'achievements' => $achievements
            ]);
    }

    public function showProfileAchievementsAction()
    {
        $user = $this->getUser();
        if (empty($user)) {
            return $this->redirect($this->generateUrl('mainpage'));
        }

        return $this->showAchievementsAction($user->getId());
    }

    public function setStudentForUserAction($id)
    {
        $user = $this->getUser();
        if (empty($user)) {
            return $this->redirect($this->generateUrl('mainpage'));
        }

        $msg = 'fail';
        $student = $this->getDoctrine()->getRepository('NetworkStatisticBundle:ParsedStudent')->find($id);
        if ($student) {
            $em = $this->getDoctrine()->getManager();
            $user->setParsedStudent(null);
            $em->persist($user);
            $em->flush();
            $user->setParsedStudent($student);
            $em->persist($user);
            $em->flush();
            $msg = 'ok';
        }
        return $this->render('NetworkWebBundle:User:msg.html.twig', [
                'msg' => 'msg.' . $msg
            ]);
    }

    public function findStudentAction($name)
    {
        $encoders = array( new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());

        $serializer = new Serializer($normalizers, $encoders);
        $students = $this->getDoctrine()->getRepository('NetworkStatisticBundle:Achievement')->findStudentByName($name);
        return new JsonResponse([
                'students' => $serializer->normalize($students)
            ]);
    }

}
