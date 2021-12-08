<?php

namespace App\Form;

use App\Entity\GeneralMeeting;
use App\Entity\ParticipantList;
use App\Repository\ParticipantListRepository;
use DateTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
        /** @var GeneralMeeting $meeting */
        $meeting=$options['data'];
        $user=$meeting->getRoom()->getEvent()->getUser();
        $builder
            ->add('name',TextType::class,array('label'=>'name'))
            ->add('date',DateTimeType::class,array(
                'label'=>'general_meeting.date','html5'=>true,
                'widget'=>'single_text',
                'data' => (is_null($meeting->getId()) ? $startDate: $meeting->getDate()),
                'with_minutes'=>false,
                'with_seconds'=>false
            ))
            ->add('count',RangeType::class,
                array('label'=>'general_meeting.votings_count',
                    'attr' => [
                        'min' => (sizeof($meeting->getMeetingVotings())>0 ? sizeof($meeting->getMeetingVotings()): 1),
                        'max' => 50,
                        'step'=>1,
                        'class'=>'custom-range'
                    ]
                    ))
            ->add('holdBack',CheckboxType::class,array(
                'label'=>'general_meeting.hold_back_available',
                'required'=>false
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
                'label'=>'general_meeting.secret_voting','required'=>false
            ))
            ->add('badVoteSettings',ChoiceType::class,array(
                'label'=>'general_meeting.bad_vote_settings.text',
                'choices'=>array(
                    'general_meeting.bad_vote_settings.allowed'=>1,
                    'general_meeting.bad_vote_settings.not_allowed'=>2,
                    'general_meeting.bad_vote_settings.allow_with_alert'=>3
                ),
                'placeholder'=>'choose'
            ))
            ->add('participantList',EntityType::class,array(
                'label'=>'participants.list.text',
                'required'=>false,
                'class'=>ParticipantList::class,
                'placeholder'=>'participants.list.choose',
                'query_builder'=>function (ParticipantListRepository $pl) use ($user) {
                    return $pl->getUsersListsQuery($user);
                },
                'choice_label' => 'name'))
            ->add('resultsForParticipants',CheckboxType::class,array(
                'label'=>'general_meeting.share_results_with_participants',
                'required'=>false
            ))
            ->add('kworum',CheckboxType::class,array(
                'label'=>'general_meeting.kworum.text','required'=>false
            ))
            ->add('kworumRequiredValue',RangeType::class,
                array('label'=>'general_meeting.kworum.required_percent',
                    'attr' => [
                        'min' => 1,
                        'max' =>100,
                        'step'=>1,
                        'class'=>'custom-range'
                    ]
                ))
            ->add('kworumType',ChoiceType::class,
                array('label'=>'general_meeting.kworum.type',
                    'choices'=>array(
                        'general_meeting.kworum.one_to_one'=>'1to1',
                        'weight.actions'=>'actions',
                        'weight.votes'=>'votes'
                    ),
                    'placeholder'=>'general_meeting.kworum.placeholder'
                ))

            /*->add('toChoose',RangeType::class,
                array('label'=>'Liczba kandydatów do wybrania',
                    'required'=>false,
                    'attr' => [
                        'min' => 1,
                        'max' => 50,
                        'step'=>1,
                        'class'=>'custom-range'
                    ]
                ))*/
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
