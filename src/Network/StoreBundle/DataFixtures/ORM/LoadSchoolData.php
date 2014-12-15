<?php

namespace Network\StoreBundle\Entity;
namespace Network\StoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Id\AssignedGenerator;

/**
 * Class LoadSchoolData
 *
 * CAUTION A LOT OF DATA
 * use "php app/console doctrine:fixtures:load --no-debug" to avoid memory limit
 *
 */
class LoadSchoolData implements FixtureInterface
{

    const MAX_ENTITIES_IN_MEMORY = 1000;

    private $entitiesInMemory;

    private function flush($manager)
    {
        $manager->flush();
        $manager->clear();
        $this->entitiesInMemory = 0;
    }

    /**
     * @param string        $entityFileName
     * @param string        $class
     * @param ObjectManager $manager
     */
    private function loadEntity($entityFileName, $class, $manager)
    {
        echo "loading $entityFileName\n";
        $resDir     = __DIR__ . '/../../Resources/DataFixtures/';
        $entityFile = fopen($resDir.$entityFileName, 'r');
        while (!feof($entityFile)) {
            $line = fgets($entityFile);
            if (trim($line) == '') {
                continue;
            }
            $entityStdClass = json_decode($line);
            $entity = new $class;
            $entity->setFromStdClass($entityStdClass, $manager);
            $manager->persist($entity);

            // hack for explicitly set Id
            $metadata = $manager->getClassMetaData(get_class($entity));
            $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
            $metadata->setIdGenerator(new AssignedGenerator());

            ++$this->entitiesInMemory;
            if ($this->entitiesInMemory > self::MAX_ENTITIES_IN_MEMORY) {
               $this->flush($manager);
            }
        }
        $this->flush($manager);
        fclose($entityFile);
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->entitiesInMemory = 0;
        $this->loadEntity('countries', 'Network\StoreBundle\Entity\Country', $manager);
        $this->loadEntity('cities', 'Network\StoreBundle\Entity\City', $manager);
        //loadEntity('regions', 'Region');
        $this->loadEntity('universities', 'Network\StoreBundle\Entity\University', $manager);
        $this->loadEntity('schools', 'Network\StoreBundle\Entity\School', $manager);
        $this->loadEntity('faculties', 'Network\StoreBundle\Entity\Faculty', $manager);
        $this->loadEntity('chairs', 'Network\StoreBundle\Entity\Chair', $manager);
    }
}
