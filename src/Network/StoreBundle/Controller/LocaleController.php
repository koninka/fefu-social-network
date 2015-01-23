<?php

namespace Network\StoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class LocaleController extends Controller
{

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function changeLocaleAction(Request $request)
    {
        $locale = $request->attributes->get('_locale');
        $request->getSession()->set('_locale', $locale);
        return $this->redirect($request->headers->get('referer'));
    }
}
