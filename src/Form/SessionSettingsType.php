<?php

namespace App\Form;

use App\Entity\SessionSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SessionSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $questions=array();
        foreach ($options['data']->getPolling()->getQuestions() as $question)
        {
            $questions[$question->getQuestionContent()]=$question->getSort();
        }
        $builder
            ->add('active_question',ChoiceType::class,array(
                'label'=>'Aktywne pytanie',
                'choices'=>$questions,
                'placeholder'=>'Wybierz aktywne pytanie'
            ))
            ->add('timeForAnswer',ChoiceType::class,array(
                'label'=>'Czas na odpowiedź',
                'choices'=>array(
                    '30 sekund'=>30,
                    '1 minuta'=>60,
                    '1.5 minuty'=>90,
                    '2 minuty'=>120
                ),
                'placeholder'=>'Wybierz czas na odpowiedź na pytanie'
            ))
            ->add('submit',SubmitType::class,array('label'=>'Zapisz'))
        ;
        if($options['data']->getStatus()==0)
        {
            $builder
                ->add('begin',CheckboxType::class,array(
                    'label'=>'Rozpocznij sesję',
                    'mapped'=>false,
                    'required'=>false)
                );
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SessionSettings::class,
        ]);
    }
}
