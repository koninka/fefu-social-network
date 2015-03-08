<?php

namespace Network\ImportBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class SyncCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setDefinition(array())->setDescription('Syncronizate data task')->setName('sync');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dataUpdater = $this->getContainer()->get('network_import.task_executor');
        $dataUpdater->execute();
        $logger = $this->getContainer()->get('logger');
        $logger->info('Sync run');
    }
}
