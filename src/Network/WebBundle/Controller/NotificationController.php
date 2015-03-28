<?php
namespace Network\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Network\StoreBundle\Entity\User;


class NotificationController extends Controller
{
    public function getNotificationAction()
    {
        $user = $this->getUser();
        if($user == null){
            return new JsonResponse(['status' => 'badUser']);
        }

        $friends = $this->getDoctrine()->getRepository('NetworkStoreBundle:Relationship')->getFriendsBirthdays($user->getId());

        $date = new \DateTime();
        $now = [
            'day' => $date->format('d'),
            'month' => $date->format('m'),
        ];

        $responseParam = [
            'today' => [],
            'tomorrow' => []
        ];

        foreach($friends as $friend){
            $birth = $friend['birthday']->format('d');
            $month = $friend['birthday']->format('m');
            $when = $birth == $now['day'] ? 'today' :'tomorrow';
            $responseParam[$when][] = [
                'id' => $friend['id'],
                'name' => $friend['firstName'] . ' ' . $friend['lastName'],
            ];

        }

        return new JsonResponse($responseParam);
    }
}
