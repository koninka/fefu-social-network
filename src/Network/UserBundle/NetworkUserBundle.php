<?php

namespace Network\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class NetworkUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
