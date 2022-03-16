<?php

namespace Adsign\FileManagerBundle\Form;

use Adsign\FileManagerBundle\Entity\Media;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class MediaGallerySearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gallery', Select2EntityType::class, [
                'multiple' => true,
                'remote_route' => 'file_manager_gallery_ajax_auto_complete',
                'class' => '\Adsign\FileManagerBundle\Entity\Gallery',
                'primary_key' => 'id',
                'text_property' => 'name',
                'minimum_input_length' => 2,
                'page_limit' => 10,
//                'allow_clear' => true,
//                'scroll' => true,
//                'delay' => 250,
//                'cache' => true,
//                'cache_timeout' => 60000, // if 'cache' is true
                'language' => 'en',
                'placeholder' => 'Search a Gallery',
                // 'object_manager' => $objectManager, // inject a custom object / entity manager
                'label' => false,
                'attr' => ['class'=> 'form-horizontal col-sm-9']
            ]
            )
//            ->add('isgallery', HiddenType::class, [
//                    'mapped' => false,
//                    'data' => 1]
//            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
        ]);
    }
}
