<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 19.02.2015
 * Time: 14:20
 */

namespace Network\ImportBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class VkontakteConfigType extends AbstractType
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
        $vkAlbumName = 'vkontakte';
        $user = $this->context->getToken()->getUser();
        $albums = $this->doctrine->getRepository('NetworkStoreBundle:UserGallery');
        $albums = $albums->findAlbumsForUser($user->getId());
        $names = array();
        if (null === $albums) {
            $names[$vkAlbumName] = $vkAlbumName;
        } else {
            foreach ($albums as $album) {
                $name = $album->getGallery()->getName();
                $names[$name] = $name;
            }
            if (!isset($names[$vkAlbumName])) {
                $names[$vkAlbumName] = $vkAlbumName;
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
            'data_class' => 'Network\ImportBundle\Utils\VkontakteImportConfig',
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'VkontakteConfigType';
    }
}
