<?php

namespace App\Form\Admin;

use App\Entity\Price;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PromoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
