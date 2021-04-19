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
            ->add('name',TextType::class,array('label'=>'Imię'))
            ->add('surname',TextType::class,array('label'=>'Nazwisko'))
            ->add('password',PasswordType::class,array('label'=>'Hasło'))
            ->add('phone',TelType::class,array('label'=>'Numer telefonu'))
            ->add('email',EmailType::class,array('label'=>'Adres email'))
            ->add('votes',NumberType::class,array(
                'label'=>"Głosy",
                'html5'=>true,
                'attr'=>[
                    'min'=>1
                ]
                ))
            ->add('actions',NumberType::class,array(
                'label'=>"Akcje",
                'html5'=>true,
                'attr'=>[
                    'min'=>1
                ]
                ))
            ->add('submit',SubmitType::class,array('label'=>'Zapisz'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
