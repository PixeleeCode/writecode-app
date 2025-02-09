<?php

namespace App\Form\Admin;

use App\Entity\Price;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PriceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', NumberType::class, [
                'required' => true,
                'label' => 'Prix de l\'abonnement',
                'attr' => [
                    'placeholder' => '0,00',
                ],
            ])
            ->add('recurring', ChoiceType::class, [
                'required' => true,
                'label' => 'Récurrence',
                'choices' => [
                    'Mensuel' => 'month',
                    'Annuel' => 'year',
                ],
                'attr' => [
                    'class' => 'appearance-none outline-none w-full bg-white text-gray-900 ring-1 ring-gray-200 text-lg rounded py-2 px-4',
                ],
            ])
            ->add('is_visible', CheckboxType::class, [
                'required' => false,
                'label' => 'Visible ?',
            ])
            ->add('promote', CheckboxType::class, [
                'required' => false,
                'label' => 'Promotionnel ?',
            ])
            ->add('promotionalText', TextType::class, [
                'required' => false,
                'label' => 'Message promotionnel',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Price::class,
        ]);
    }
}
