<?php

namespace Network\ImportBundle;

use Network\ImportBundle\DependencyInjection\NetworkImportExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NetworkImportBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new NetworkImportExtension();
    }
}
