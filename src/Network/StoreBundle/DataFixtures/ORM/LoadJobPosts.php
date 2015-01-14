<?php
namespace Network\StoreBundle\Entity;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Network\StoreBundle\Entity\JobPost;


class LoadJobPosts implements FixtureInterface, ContainerAwareInterface
{

    protected $container = null;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        return $this;
    }

    public function load(ObjectManager $manager)
    {
        $filePath = $this->container
                         ->get('kernel')
                         ->locateResource('@NetworkStoreBundle/Resources/DataFixtures/posts');
        $posts = array_map('trim', explode("\n", file_get_contents($filePath)));
        $id = 0;

        foreach ($posts as $postName) {
            $post = new JobPost();
            $post->setId($id++)
                 ->setName($postName)
                 ->setPredefined(true);
            $manager->persist($post);
        }

        $manager->flush();
    }

} 
