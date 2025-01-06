<?php

namespace App\Form;

use App\Entity\City;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null,[
                    'required' => false,
                    'label' => 'Nom de la ville',
                    'attr' => ['class' => 'form-control', 'placeholder' => 'Nom de la ville']
                ])
            ->add('shippingCost', null,[
                    'label' => 'Frais de port',
                    'attr' => ['class' => 'form-control', 'placeholder' => 'Frais de port']
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => City::class,
        ]);
    }
}
