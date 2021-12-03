<?php

namespace App\Form;

use App\Entity\Polling;
use App\Entity\Question;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $question=$options['data'] ?? null;
        $isEdit= $question && $question->getId();
        /**
         * @var $polling Polling
         */
        $polling=$question->getPolling();
        $count=sizeof($polling->getQuestions());
        $builder
            ->add('question_content',TextType::class,array('label'=>'question.text'))
            ->add('answers',CollectionType::class,[
                'label'=>'answers',
                'entry_type'=>AnswerType::class,
                'entry_options'=>['label'=>false],
                'allow_add'=>true,
                'allow_delete'=>true,
                'by_reference' => false
            ])

            ->add('submit',SubmitType::class,array('label'=>"save"))
        ;
        if(!$isEdit&&($count+1)<$polling->getQuestionsCount())
        {
            $builder
                ->add('next',CheckboxType::class,
                    array('label'=>'add_next','mapped'=>false,'required'=>false,'value'=>1,'attr'=>array(
                        'checked'=>true
                    ))
                );
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
        ]);
    }
}
