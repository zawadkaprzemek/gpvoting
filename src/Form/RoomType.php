<?php

namespace App\Form;

use App\Entity\EventCode;
use App\Entity\Room;
use App\Repository\EventCodeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $event=$options['data']->getEvent();
        $builder
            ->add('name',TextType::class,array('label'=>'Nazwa'))
            ->add('code',EntityType::class,array(
                'label'=>'Kod dostępu',
                'placeholder'=>'Wybierz kod dostępu',
                'class'=>EventCode::class,
                'query_builder'=>function (EventCodeRepository $ec) use ($event) {
                    return $ec->getEventCodesQuery($event);
                },
                'choice_label' => 'name',
            ))
            ->add('submit',SubmitType::class,array('label'=>'Zapisz'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Room::class,
        ]);
    }
}
