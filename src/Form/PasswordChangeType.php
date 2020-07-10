<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PasswordChangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('oldPassword',PasswordType::class,array('label'=>'Stare hasło'))
            ->add('plainPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'first_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Prosze wprowadzić hasło',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Hasło musi się składać conajmniej z  {{ limit }} znaków',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                    ],
                    'label' => 'Nowe hasło',
                ],
                'second_options' => [
                    'label' => 'Powtórz hasło',
                ],
                'invalid_message' => 'Hasła muszą się zgadzać',
                // Instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
            ))
            ->add('submit',SubmitType::class,array('label'=>'Zapisz'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
