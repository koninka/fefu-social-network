<?php
namespace Network\ImportBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Created by PhpStorm.
 * User: user
 * Date: 13.02.2015
 * Time: 0:51
 */

class InstagramConfigType extends AbstractType
{
    private $context;
    private $doctrine;

    function __construct($context, $doctrine)
    {
        $this->context = $context;
        $this->doctrine = $doctrine;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $instagramAlbumName = 'Instagram';
        $user = $this->context->getToken()->getUser();
        $albums = $this->doctrine->getRepository('NetworkStoreBundle:UserGallery');
        $albums = $albums->findAlbumsForUser($user->getId());
        $names = array();
        if (null === $albums) {
            $names[$instagramAlbumName] = $instagramAlbumName;
        } else {
            foreach ($albums as $album) {
                $name = $album->getGallery()->getName();
                $names[$name] = $name;
            }
            !isset($names[$instagramAlbumName]) ? $names[$instagramAlbumName] = $instagramAlbumName : 1;
        }
        $builder
            ->add('album', 'choice', array(
                'choices' => $names,

            ));
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Network\ImportBundle\Utils\InstagramImportConfig',
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'InstagramConfigType';
    }
}
