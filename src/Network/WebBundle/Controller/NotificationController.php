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

        $friends = $this->getDoctrine()->getRepository('NetworkStoreBundle:Relationship')->findFriendsForUser($user->getId());
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
            $f = $friend->getPartner();
            $birth = $f->getBirthday()->format('d');
            $month = $f->getBirthday()->format('m');
            if($month == $now['month']) {
                $when = $birth == $now['day'] ? 'today' : ($birth - 1 == $now['day'] ? 'tomorrow' : null);
                if ($when == null)
                    continue;

                $responseParam[$when][] = [
                    'id' => $f->getId(),
                    'name' => $f->getFirstName() . ' ' . $f->getLastName(),
                ];
            } elseif(($month - 1 == $now['month'] || $now['month'] == 12 && $month == 1) &&
                (date('t', strtotime('today')) == $now['day'] && $birth == 1) ){         // Today the last day of the month
                $responseParam['tomorrow'][] = [                                         // And Birthday will be tomorrow
                    'id' => $f->getId(),
                    'name' => $f->getFirstName() . ' ' . $f->getLastName(),
                ];
            }
        }

        return new JsonResponse($responseParam);
    }
}
