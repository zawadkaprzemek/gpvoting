<?php

namespace App\Form;

use App\Entity\Pack;
use App\Entity\User;
use App\Repository\PackRepository;
use Gregwar\CaptchaBundle\Type\CaptchaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
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
            ->add('clientName',TextType::class,array('label'=>'register.form.client_name.label'))
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label'=>'register.form.terms.label',
                'constraints' => [
                    new IsTrue([
                        'message' => 'register.form.terms.error',
                    ]),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'first_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'register.form.password.blank',
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'register.form.password.minMessage',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                    ],
                    'label' => 'password',
                ],
                'second_options' => [
                    'label' => 'password.repeat',
                ],
                'invalid_message' => 'register.form.password.not-equal',
                // Instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
            ))

            ->add('name',TextType::class,array('label'=>'firstName',))
            ->add('surname',TextType::class,array('label'=>'lastName'))
            ->add('email',EmailType::class,array('label'=>'email.address'))
            ->add('participantListSize', NumberType::class,['label' => 'participants.limit_list',
                'html5' =>true,
                'attr'=>[
                    'min'=>1,
                    'step' =>1,
                    'max'=> 50,
                ],
                'empty_data' => 10
            ])
            ->add('eventsLimit', NumberType::class,['label' => 'profile.events_count',
                'html5' =>true,
                'mapped' =>false,
                'attr'=>[
                    'min'=>0,
                    'step' =>0,
                    'max'=> 0,
                    'readonly'=> true
                ],
                'data' => 0
            ])
            ->add('participantsListCount', NumberType::class,['label' => 'profile.participants_list_count',
                'html5' =>true,
                'mapped' =>false,
                'attr'=>[
                    'min'=>0,
                    'step' =>0,
                    'max'=> 0,
                    'readonly'=> true
                ],
                'data' => 0
            ])
            ->add('pack', EntityType::class,[
                'label'=>'packs.single_text',
                'attr' =>[
                    'readonly'=> true
                ],
                'required'=>false,
                'class'=>Pack::class,
                'placeholder'=>false,
                'choice_label' => 'name',
                'empty_data' => function (PackRepository $pr)  {
                    return $pr->getSingleStarterPack();
                },
            ])
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
