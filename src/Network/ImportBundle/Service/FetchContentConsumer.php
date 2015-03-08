<?php
namespace Network\ImportBundle\Service;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;


class FetchContentConsumer implements ConsumerInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function execute(AMQPMessage $msg)
    {
        $em = $this->container
                   ->get('doctrine')
                   ->getManager();
        $loader = $this->container->get('network_import.content_loader');
        $items = $em->createQuery($msg->body)
                    ->getResult();

        foreach ($items as $item) {
            $loader->getItemContent($item);
        }

        $em->flush();
    }
}
