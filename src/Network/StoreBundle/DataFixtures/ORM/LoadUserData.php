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

    // Percent of Russians
    const USER_PURITY = 0.666;

    private $container;
    private $manager;

    private function addUser($username, $password, $gender, $firstName,
        $lastName, $email, $birthday)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->createUser();
        $encoder = $this->container
                        ->get('security.encoder_factory')
                        ->getEncoder($user);

        $user->setUsername($username)
             ->setPassword($password)
             ->setGender($gender)
             ->setFirstName($firstName)
             ->setLastName($lastName)
             ->setEmail($email)
             ->setBirthday($birthday)
             ->setEnabled(true);

        $user->hash($encoder);
        $userManager->updateUser($user, true);
        $this->manager->persist($user);
        $this->manager->flush();
    }

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
        $this->addUser('admin', 'password', 'male', 'John', 'Doe', 'admin', null);

        $resDir = __DIR__ . '/../../Resources/DataFixtures/';

        $firstNamesFemale = file($resDir . 'first-name-female');
        $firstNamesMale = file($resDir . 'first-name-male');
        $firstNamesFemaleRussian = file($resDir . 'first-name-female-russian');
        $firstNamesMaleRussian = file($resDir . 'first-name-male-russian');

        $lastNames = file($resDir . 'last-name');
        $lastNamesRussian = file($resDir . 'last-name-russian');
        $lastNamesFemaleRussian = file($resDir . 'last-name-female-russian');
        $lastNamesMaleRussian = file($resDir . 'last-name-male-russian');

        $genders = ['male', 'female'];
        $emailProviders = ['@gmail.com', '@hotmail.com', '@yandex.ru', '@mail.com'];

        for ($i = 0; $i < LoadUserData::USER_COUNT; $i++) {
            $gender = $genders[array_rand($genders)];

            if ((rand() / getrandmax()) < LoadUserData::USER_PURITY) {
                $emailProvider = '@почта.рф';

                if ($gender == 'male') {
                    $firstNameSource = $firstNamesMaleRussian;
                    $lastNameSource = $lastNamesMaleRussian;

                } else {
                    $firstNameSource = $firstNamesFemaleRussian;
                    $lastNameSource = $lastNamesFemaleRussian;
                }

                if (rand() / getrandmax() < 0.5) {
                    $lastNameSource = $lastNamesRussian;
                }

            } else {
                $emailProvider = $emailProviders[array_rand($emailProviders)];

                if ($gender == 'male') {
                    $firstNameSource = $firstNamesMale;

                } else {
                    $firstNameSource = $firstNamesFemale;
                }

                $lastNameSource = $lastNames;
            }

            $firstName = rtrim($firstNameSource[array_rand($firstNameSource)]);
            $lastName = rtrim($lastNameSource[array_rand($lastNameSource)]);

            $email = str_replace(' ', '', $firstName)
                . '.'
                . str_replace(' ', '', $lastName)
                . $emailProvider;

            $birthday = new \DateTime();
            $birthday->setDate(rand(1894, 2014), rand(1, 12), rand(1, 28));

            $this->addUser('user-' . $i, 'secret-' . $i, $gender, $firstName,
                $lastName, $email, $birthday);
        }
    }
}
