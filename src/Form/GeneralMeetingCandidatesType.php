<?php

namespace App\Form;

use App\Entity\GeneralMeeting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GeneralMeetingCandidatesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /**
         * @var $meeting GeneralMeeting
         */
        $meeting=$options['data'];
        $builder
            ->add('toChoose',RangeType::class,
                array('label'=>'Ilość kandydatów do wybrania',
                    'attr' => [
                        'min' => 1,
                        'max' => $meeting->getCount(),
                        'step'=>1,
                        'class'=>'custom-range'
                    ]
                ))
            ->add('candidates',CollectionType::class,[
                'label'=>'Kandydaci',
                'entry_type'=>CandidateType::class,
                'entry_options'=>['label'=>false],
                'allow_add'=>false,
                'allow_delete'=>false,
                'by_reference' => false
            ])
            ->add('submit',SubmitType::class,array('label'=>'Zapisz'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GeneralMeeting::class,
        ]);
    }
}
