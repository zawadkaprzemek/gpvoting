<?php

namespace App\Form;

use App\Entity\Event;
use App\Repository\EventRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $event=$options['data'] ?? null;
        $isEdit= $event && $event->getId();
        $builder
            ->add('name',TextType::class,array('label'=>'event.name.label'))
            ->add('shortOrganizatorName',TextType::class,array('label'=>'event.shortOrganizatorName.label'))
            ->add('logoFile',FileType::class,
                array(
                    'label'=>'event.logo.label',
                    'mapped'=>false,
                    'required'=>!$isEdit,
                    'constraints' => [
                        new Image([
                            'maxSize' => '1024k',
                            'minWidth' => 200,
                            'maxWidth' => 400,
                            'minHeight' => 200,
                            'maxHeight' => 400,
                        ])
                    ]
                )
            )
            ->add('submit',SubmitType::class,array('label'=>'save'))
        ;
        if($isEdit)
        {
            $builder
                ->add('changeLogo',CheckboxType::class,array('label'=>'event.changeLogo.label','required'=>false,'mapped'=>false));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
