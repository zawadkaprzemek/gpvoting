<?php

namespace App\Form;

use App\Entity\User;
use Gregwar\CaptchaBundle\Type\CaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var User $user */
        $user=$options['data'];
        $builder
            ->add('username',TextType::class,array('label'=>'register.form.username.label'))
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label'=>'register.form.terms.label',
                'constraints' => [
                    new IsTrue([
                        'message' => 'register.form.terms.error',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'label'=>'password',
                'constraints' => [
                    new NotBlank([
                        'message' => 'register.form.password.blank',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'register.form.password.minMessage',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('name',TextType::class,array('label'=>'firstName',))
            ->add('surname',TextType::class,array('label'=>'lastName'))
            ->add('email',EmailType::class,array('label'=>'email.address'))
            ->add('submit',SubmitType::class,array(
                'label'=>($user->getParent()===null ? 'registration' :'profile.subaccount.create.button')

            ))
        ;
        if($user->getParent()===null)
        {
            $builder
                ->add('captcha',CaptchaType::class,array('label'=>'captcha'));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
