<?php

namespace App\Form;

use App\Entity\GeneralMeeting;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GeneralMeetingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $startDate= new DateTime("+ 1 hour");
        $startDate->setTime($startDate->format('H'),0, 0,0);

        $builder
            ->add('name',TextType::class,array('label'=>'Nazwa'))
            ->add('startDate',DateTimeType::class,array(
                'label'=>'Data zgromadzenia','html5'=>true,
                'widget'=>'single_text',
                'data' => (is_null($options['data']->getId()) ? $startDate: $options['data']->getStartDate()),
                'with_minutes'=>false,
                'with_seconds'=>false
            ))
            ->add('variant',ChoiceType::class,array(
                    'label'=>'Wariant zgromadzenia',
                    'choices'=>array(
                        'Głosowanie nad uchwałą'=>1,
                        'Głosowanie personalne'=>2
                    ),
                    'placeholder'=>'Wybierz wariant'
                ))
            ->add('countResolution',RangeType::class,
                array('label'=>'Ilość uchwał',
                    'attr' => [
                        'min' => 1,
                        'max' => 99,
                        'step'=>1,
                        'class'=>'custom-range'
                    ],
                    'mapped'=>false,
                    'required'=>false
                    ))
            ->add('countPersonal',RangeType::class,
                array('label'=>'Ilość kandydatów',
                    'attr' => [
                        'min' => 2,
                        'max' => 50,
                        'step'=>1,
                        'class'=>'custom-range'
                    ],
                    'mapped'=>false,
                    'required'=>false
                    ))
            ->add('holdBack',CheckboxType::class,array(
                'label'=>'Dostępna opcja wstrzymuje się',
                'required'=>false
                ))
            ->add('weight',ChoiceType::class,array(
                'label'=>'Waga głosów',
                'choices'=>array(
                    'Waga głosów'=>1,
                    'Waga akcji'=>2
                ),
                'placeholder'=>'Wybierz typ wagi'
            ))
            ->add('secret',CheckboxType::class,array(
                'label'=>'Tajne głosowanie','required'=>false
            ))
            ->add('badVoteSettings',ChoiceType::class,array(
                'label'=>'Błędne głosy',
                'required'=>false,
                'choices'=>array(
                    'Pozwól, nie ostrzegaj'=>1,
                    'Nie pozwól, blokuj'=>2,
                    'Ostrzegaj ale pozwól'=>3
                ),
                'placeholder'=>'Wybierz'
            ))
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
