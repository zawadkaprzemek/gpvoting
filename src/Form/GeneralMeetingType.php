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
                    'disabled'=>sizeof($meeting->getCandidates())>0||sizeof($meeting->getResolutions())>0,
                    'choices'=>array(
                        'Głosowanie nad uchwałą'=>1,
                        'Głosowanie personalne'=>2
                    ),
                    'placeholder'=>'Wybierz wariant'
                ))
            ->add('countResolution',RangeType::class,
                array('label'=>'Ilość uchwał',
                    'attr' => [
                        'min' => (sizeof($meeting->getResolutions())>0 ? sizeof($meeting->getResolutions()): 1),
                        'max' => 99,
                        'step'=>1,
                        'class'=>'custom-range'
                    ],
                    'data'=>(sizeof($meeting->getResolutions())>0 ? sizeof($meeting->getResolutions()): $meeting->getCount()),
                    'mapped'=>false,
                    'required'=>false
                    ))
            ->add('countPersonal',RangeType::class,
                array('label'=>'Ilość kandydatów',
                    'attr' => [
                        'min' => (sizeof($meeting->getCandidates())>0 ? sizeof($meeting->getCandidates()): 2),
                        'max' => 50,
                        'step'=>1,
                        'class'=>'custom-range'
                    ],
                    'data'=>(sizeof($meeting->getCandidates())>0 ? sizeof($meeting->getCandidates()): $meeting->getCount()),
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
            ->add('participantList',EntityType::class,array(
                'label'=>'Lista uczestników',
                'required'=>false,
                'class'=>ParticipantList::class,
                'placeholder'=>'Wybierz listę uczestników',
                'query_builder'=>function (ParticipantListRepository $pl) use ($user) {
                    return $pl->getUsersListsQuery($user);
                },
                'choice_label' => 'name'))
            ->add('toChoose',RangeType::class,
                array('label'=>'Liczba kandydatów do wybrania',
                    'required'=>false,
                    'attr' => [
                        'min' => 1,
                        'max' => 50,
                        'step'=>1,
                        'class'=>'custom-range'
                    ]
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
