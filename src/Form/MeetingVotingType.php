<?php

namespace App\Form;

use App\Entity\MeetingVoting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeetingVotingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var MeetingVoting $voting */
        $voting=$options['data'];
        $meeting=$voting->getMeeting();
        $builder
            ->add('content',TextareaType::class,array('label'=>'Treść'))
            ->add('type',ChoiceType::class,array('label'=>'Typ głosowania',
                'choices'=>array(
                    'Głosowanie nad uchwałą'=>1,
                    'Głosowanie personalne'=>2,
                    'Głosowanie sondażowe'=>3
                ),
                'placeholder'=>'Wybierz typ głosowania'
            ))
            ->add('tochoose',RangeType::class,array('label'=>'Do wyboru',
                'required'=>false,
                'attr'=>array(
                    'min'=>1,
                    'max'=>(sizeof($voting->getCandidates())),
                    'step'=>1
                )
            ))
            ->add('weight',ChoiceType::class,array(
                'label'=>'Waga głosów',
                'choices'=>array(
                    'Waga głosów'=>1,
                    'Waga akcji'=>2
                ),
                'placeholder'=>'Wybierz typ wagi'
            ))
            ->add('multiChoose',CheckboxType::class,array('label'=>'Wielokrotny wybór','required'=>false))
            ->add('submit',SubmitType::class,array('label'=>'Zapisz'))
            ->add('candidates',CollectionType::class,[
                'label'=>'Kandydaci',
                'entry_type'=>CandidateType::class,
                'entry_options'=>['label'=>false],
                'allow_add'=>true,
                'allow_delete'=>true,
                'by_reference' => false,
                'required'=>false
            ])
            ->add('answers',CollectionType::class,[
                'label'=>'Odpowiedzi',
                'entry_type'=>MeetingAnswerType::class,
                'entry_options'=>['label'=>false],
                'allow_add'=>true,
                'allow_delete'=>true,
                'by_reference' => false,
                'required'=>false
            ])
        ;
        if(is_null($voting->getId()))
        {
            $builder->add('add_next',CheckboxType::class,
                array(
                    'label'=>'Dodaj następne',
                    'mapped'=>false,
                    'required'=>false,
                    'disabled'=>(sizeof($meeting->getMeetingVotings())+1)==$meeting->getCount()
                ));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MeetingVoting::class,
        ]);
    }
}
