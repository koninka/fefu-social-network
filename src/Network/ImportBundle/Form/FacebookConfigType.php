<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 28.02.2015
 * Time: 12:04
 */

namespace Network\ImportBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FacebookConfigType extends AbstractType
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
        $AlbumName = 'facebook';
        $user = $this->context->getToken()->getUser();
        $albums = $this->doctrine->getRepository('NetworkStoreBundle:UserGallery');
        $albums = $albums->findAlbumsForUser($user->getId());
        $names = array();
        if ($albums === null) {
            $names[$AlbumName] = $AlbumName;
        } else {
            foreach ($albums as $album) {
                $name = $album->getGallery()->getName();
                $names[$name] = $name;
            }
            if (!isset($namse[$AlbumName])) {
                $names[$AlbumName] = $AlbumName;
            }
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
            'data_class' => 'Network\ImportBundle\Utils\FacebookImportConfig',
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'FacebookConfigType';
    }
}
