<?php

namespace App\Form;

use App\Entity\Participant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',TextType::class,array('label'=>'firstName'))
            ->add('surname',TextType::class,array('label'=>'lastName'))
            ->add('password',PasswordType::class,array('label'=>'password'))
            ->add('phone',TelType::class,array('label'=>'phone'))
            ->add('email',EmailType::class,array('label'=>'email.address'))
            ->add('votes',NumberType::class,array(
                'label'=>"votes",
                'html5'=>true,
                'attr'=>[
                    'min'=>1
                ]
                ))
            ->add('actions',NumberType::class,array(
                'label'=>"actions",
                'html5'=>true,
                'attr'=>[
                    'min'=>1
                ]
                ))
            ->add('submit',SubmitType::class,array('label'=>'save'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
