<?php

namespace App\Form;

use App\Entity\Question;
use App\Entity\Vote;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $vote=$options['data'] ?? null;
        /**
         * @var $vote Vote
         */
        //$isMulti= $vote && $vote->getQuestion()->getMultiChoice();
        $startDate=$vote->getStartDate()->format('Y-m-d H:i:s.u');
        /*if($isMulti)
        {
            $builder->add('answer',CheckboxType::class,array('label'=>null,'mapped'=>false,'required'=>false));
        }else{
            $builder->add('answer',RadioType::class,array('label'=>null,'mapped'=>false));
        }*/
        $builder
            ->add('startDateString',HiddenType::class,array('data'=>$startDate,'mapped'=>false))
            ->add('submit',SubmitType::class,array('label'=>'vote.text'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Vote::class,
            'allow_extra_fields'=>true
        ]);
    }
}
