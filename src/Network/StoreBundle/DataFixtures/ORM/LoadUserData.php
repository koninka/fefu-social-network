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
    const USER_COUNT = 1000;
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $resDir = __DIR__ . '/../../Resources/DataFixtures/';
        $firstNamesFemale = file($resDir . 'first-name-female');
        $firstNamesMale = file($resDir . 'first-name-male');
        $lastNames = file($resDir . 'last-name');
        $genders = ['male', 'female'];
        $emailProviders = ['@gmail.com', '@hotmail.com', '@yandex.ru', '@почта.рф', '@mail.com'];

        for ($i = 0; $i < LoadUserData::USER_COUNT; $i++) { 
            $user = new User();

            $gender = $genders[array_rand($genders)];

            $firstName = $firstNamesMale[array_rand($firstNamesMale)];
            if ($gender == 'female') {
                $firstName = $firstNamesFemale[array_rand($firstNamesFemale)];
            }

            $lastName = $lastNames[array_rand($lastNames)];
            $email = str_replace(' ', '', $firstName)
                . '.'
                . str_replace(' ', '', $lastName)
                . $emailProviders[array_rand($emailProviders)];
            
            $user->setUsername('user-' . $i);
            $user->setSalt(md5(uniqid()));
            $encoder = $this->container
                            ->get('security.encoder_factory')
                            ->getEncoder($user);
            $user->setPassword($encoder->encodePassword('secret-' . $i, $user->getSalt()));
            
            $user->setGender($gender)
                 ->setFirstName($firstName)
                 ->setLastName($lastName)
                 ->setEmail($email);

            $birthday = new \DateTime();
            $birthday->setDate(rand(1894, 2014), rand(1, 12), rand(1, 28));
            $user->setBirthday($birthday);

            $manager->persist($user);
            $manager->flush();
        }
    }
}
