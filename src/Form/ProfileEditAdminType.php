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
            ->add('username',TextType::class,array('label'=>'login.text'))
            ->add('name',TextType::class,array('label'=>'firstName'))
            ->add('surname',TextType::class,array('label'=>'lastName'))
            ->add('email',EmailType::class,array('label'=>'email.address'))
            ->add('participantListSize',NumberType::class,array(
                'label'=>'participants.limit_list',
                'html5'=>true,
                'attr'=>[
                    'min'=>5,
                    'step'=>1
                ]
            ))
            ->add('pack',EntityType::class,array(
                'label'=>'packs.single_text',
                'class'=>Pack::class,
                'choice_label'=>'name'
                ))
            ->add('submit',SubmitType::class,array('label'=>'save'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
