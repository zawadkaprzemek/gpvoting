<?php

namespace App\Form;

use App\Entity\EventCode;
use App\Entity\Polling;
use App\Repository\EventCodeRepository;
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

class PollingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $event=$options['data']->getRoom()->getEvent();
        $startDate= new DateTime("+ 1 hour");
        $startDate->setTime($startDate->format('H'),$startDate->format('i'), 0,0);
        $endDate= clone $startDate;
        $endDate->modify("+1 month");
        $builder
            ->add('name',TextType::class,array('label'=>'Nazwa'))
            ->add('questionsCount',RangeType::class,array('label'=>'Ilość pytań','attr' => [
                'min' => 1,
                'max' => 50,
                'step'=>1,
                'class'=>'custom-range'
            ]))
            ->add('default_answers',RangeType::class,array('label'=>'Domyślna ilość odpowiedzi',
                'attr' => [
                    'min' => 2,
                    'max' => 6,
                    'step'=>1,
                    'class'=>'custom-range'
                ]))
            ->add('session',CheckboxType::class,array('label'=>'Głosowanie sesyjne','required'=>false))
            ->add('startDate',DateTimeType::class,array(
                'label'=>'Data początkowa',
                'html5'=>true,
                'widget'=>'single_text',
                'data' => (is_null($options['data']->getId()) ? $startDate: $options['data']->getStartDate()),
                'with_minutes'=>false,
                'with_seconds'=>false
                ))
            ->add('endDate',DateTimeType::class,array(
                'label'=>'Data zakończenia',
                'required'=>false,
                'html5'=>true,
                'widget'=>'single_text',
                'data' => (is_null($options['data']->getEndDate()) ? $endDate : $options['data']->getEndDate()),
                'with_minutes'=>false,
                'with_seconds'=>false
                ))
            ->add('code',EntityType::class,array(
                'label'=>'Kod dostępu',
                'required'=>false,
                'class'=>EventCode::class,
                'placeholder'=>'Wybierz kod dostępu',
                'query_builder'=>function (EventCodeRepository $ec) use ($event) {
                    return $ec->getEventCodesQuery($event);
                },
                'choice_label' => 'name'))
            ->add('resultsGraph',ChoiceType::class,array(
                'label'=>'Przedstawienie wyników',
                'choices'=>array(
                    'Wykres pionowy'=>1,
                    'Wykres poziomy'=>2
                ),
                'placeholder'=>'Wybierz sposób przedstawienia wyników'
                ))
            ->add('submit',SubmitType::class,array('label'=>'Zapisz'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Polling::class,
        ]);
    }
}
