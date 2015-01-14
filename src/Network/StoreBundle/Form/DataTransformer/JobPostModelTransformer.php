<?php
namespace Network\StoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Network\StoreBundle\Entity\JobPost;

class JobPostModelTransformer implements DataTransformerInterface
{

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Transform an object (JobPost) to string (name of JobPost).
     *
     * @param JobPost|null $data
     * @return string
     */
    public function transform($data)
    {
        return null === $data ? '' : $data->getName();
    }

    /**
     * Transform a string to JobPost object using search by name.
     * If object with specified name doesn't exist, create it.
     *
     * @param string $name
     * @return JobPost|null
     */
    public function reverseTransform($name)
    {
        $name = trim($name);

        if (strlen($name) == 0) {
            return null;
        }

        $objects = $this->om->getRepository('NetworkStoreBundle:JobPost')->findBy([
            'name' => $name,
        ]);

        if (count($objects) === 0) {
            $object = new JobPost();
            $object->setName($name)->setPredefined(false);
            $this->om->persist($object);
            $this->om->flush();
        } else {
            $object = $objects[0];
        }

        return $object;
    }

} 
