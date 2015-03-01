<?php

namespace Network\OpenIdBundle;

use Network\OpenIdBundle\DependencyInjection\NetworkOpenIdExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NetworkOpenIdBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new NetworkOpenIdExtension();
    }
}

