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
                'label'=>'session.active_question',
                'choices'=>$questions,
                'choice_translation_domain'=>false,
                'placeholder'=>'session.choose_actiove_question'
            ))
            ->add('timeForAnswer',ChoiceType::class,array(
                'label'=>'session.time_for_answer',
                'choices'=>array(
                    '30_sek'=>30,
                    '1_min'=>60,
                    '1_5_min'=>90,
                    '2_min'=>120
                ),
                'placeholder'=>'session.time_for_answer_choose'
            ))
            ->add('submit',SubmitType::class,array('label'=>'save'))
        ;
        if($options['data']->getStatus()==0)
        {
            $builder
                ->add('begin',CheckboxType::class,array(
                    'label'=>'session.begin.button',
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
