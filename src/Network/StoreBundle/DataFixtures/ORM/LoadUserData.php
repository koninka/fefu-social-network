<?php

namespace Network\StoreBundle\Entity;
namespace Network\StoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Network\StoreBundle\Entity\User;

class LoadUserData implements FixtureInterface, ContainerAwareInterface
{
    const USER_COUNT = 128;
    private $container;
    private $manager;

    private function setManager($manager)
    {
        $this->manager = $manager;

        return $this;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        return $this;
    }

    public function load(ObjectManager $manager)
    {
        $this->setManager($manager);
        $resDir = __DIR__ . '/../../Resources/DataFixtures/';
        $firstNamesFemale = file($resDir . 'first-name-female');
        $firstNamesMale = file($resDir . 'first-name-male');
        $lastNames = file($resDir . 'last-name');
        $genders = ['male', 'female'];
        $emailProviders = ['@gmail.com', '@hotmail.com', '@yandex.ru', '@почта.рф', '@mail.com'];

        for ($i = 0; $i < LoadUserData::USER_COUNT; $i++) {
            $user = new User();

            $gender = $genders[array_rand($genders)];

            if ($gender == 'female') {
                $firstName = $firstNamesFemale[array_rand($firstNamesFemale)];
            } else {
                $firstName = $firstNamesMale[array_rand($firstNamesMale)];
            }

            $lastName = $lastNames[array_rand($lastNames)];

            $email = str_replace(' ', '', $firstName)
                . '.'
                . str_replace(' ', '', $lastName)
                . $emailProviders[array_rand($emailProviders)];

            $encoder = $this->container
                            ->get('security.encoder_factory')
                            ->getEncoder($user);

            $birthday = new \DateTime();
            $birthday->setDate(rand(1894, 2014), rand(1, 12), rand(1, 28));

            $user->setUsername('user-' . $i)
                 ->setPassword('secret-' . $i)
                 ->setGender($gender)
                 ->setFirstName($firstName)
                 ->setLastName($lastName)
                 ->setEmail($email)
                 ->setBirthday($birthday);

            $user->hash($encoder);

            $manager->persist($user);
            $manager->flush();
        }
    }
}
