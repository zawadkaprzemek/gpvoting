<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddCodesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('count', NumberType::class, array('label' => 'codes.manage.new_codes.count', 'html5' => true, 'data' => 1, 'attr' => ['min' => 1]))
            ->add('prefix', TextType::class, array(
                'label' => 'codes.manage.new_codes.prefix',
                'attr' => [
                    'min-length' => 3
                ]
            ))
            ->add('randomLength', NumberType::class, array(
                    'label' => 'codes.manage.new_codes.randomLengthsize',
                    'html5' => true,
                    'data' => 4,
                    'attr' => [
                        'min' => 4,
                        'max' => 20,
                        'step' => 1
                    ])
            )
            ->add('submit', SubmitType::class, array('label' => 'codes.manage.new_codes.generate'));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
