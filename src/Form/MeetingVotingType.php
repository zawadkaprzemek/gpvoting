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
            ->add('content',TextareaType::class,array('label'=>'content'))
            ->add('type',ChoiceType::class,array('label'=>'voting_type',
                'choices'=>array(
                    'general_meeting.voting.resolution'=>1,
                    'general_meeting.voting.personal'=>2,
                    'general_meeting.voting.poll'=>3
                ),
                'placeholder'=>'general_meeting.voting.choose_type'
            ))
            ->add('tochoose',RangeType::class,array('label'=>'general_meeting.voting.tochoose',
                'required'=>false,
                'attr'=>array(
                    'min'=>1,
                    'max'=>(sizeof($voting->getCandidates())),
                    'step'=>1
                )
            ))
            ->add('weight',ChoiceType::class,array(
                'label'=>'weight.votes',
                'choices'=>array(
                    'weight.votes'=>1,
                    'weight.actions'=>2
                ),
                'placeholder'=>'general_meeting.choose_type_weight'
            ))
            ->add('secret',CheckboxType::class,array(
                'label'=>'general_meeting.secret_voting',
                'required'=>false,
                'disabled' => $meeting->getSecret()
            ))
            ->add('multiChoose',CheckboxType::class,array('label'=>'multiple_choise','required'=>false))
            ->add('submit',SubmitType::class,array('label'=>'save'))
            ->add('candidates',CollectionType::class,[
                'label'=>'candidates',
                'entry_type'=>CandidateType::class,
                'entry_options'=>['label'=>false],
                'allow_add'=>true,
                'allow_delete'=>true,
                'by_reference' => false,
                'required'=>false
            ])
            ->add('answers',CollectionType::class,[
                'label'=>'answers',
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
                    'label'=>'add_next',
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
