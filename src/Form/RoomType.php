<?php

namespace App\Form;

use App\Entity\Room;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Room $room */
        $room = $options['data'];
        $builder
            ->add('name', TextType::class, array('label' => 'name'))
            ->add('submit', SubmitType::class, array('label' => 'save'));

        if ($room->getId() === null) {
            $builder
                ->add('prefix', TextType::class, array(
                    'label' => 'room.add.code.prefix',
                    'mapped' => false,
                    'attr' => [
                        'min-length' => 3
                    ]
                ))
                ->add('randomLength', NumberType::class, array(
                        'label' => 'room.add.code.randomLength',
                        'mapped' => false,
                        'html5' => true,
                        'data' => 4,
                        'attr' => [
                            'min' => 4,
                            'max' => 20,
                            'step' => 1
                        ])
                );
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Room::class,
        ]);
    }
}
