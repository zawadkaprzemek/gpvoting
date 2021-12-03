<?php

namespace App\Form;

use App\Entity\GeneralMeeting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GeneralMeetingResolutionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('resolutions',CollectionType::class,[
                'label'=>'resolutions',
                'entry_type'=>ResolutionType::class,
                'entry_options'=>['label'=>false],
                'allow_add'=>false,
                'allow_delete'=>false,
                'by_reference' => false
            ])
            ->add('submit',SubmitType::class,array('label'=>'save'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GeneralMeeting::class,
        ]);
    }
}
