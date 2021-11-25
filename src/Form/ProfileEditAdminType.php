<?php

namespace App\Form;

use App\Entity\Pack;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileEditAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username',TextType::class,array('label'=>'Login'))
            ->add('name',TextType::class,array('label'=>'Imię'))
            ->add('surname',TextType::class,array('label'=>'Nazwisko'))
            ->add('email',EmailType::class,array('label'=>'Adres e-mail'))
            ->add('participantListSize',NumberType::class,array(
                'label'=>'Limit wielkości listy uczestników',
                'html5'=>true,
                'attr'=>[
                    'min'=>5,
                    'step'=>1
                ]
            ))
            ->add('pack',EntityType::class,array(
                'label'=>'user.pack',
                'class'=>Pack::class,
                'choice_label'=>'name'
                ))
            ->add('submit',SubmitType::class,array('label'=>'Zapisz'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
