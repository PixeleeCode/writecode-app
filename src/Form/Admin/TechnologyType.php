<?php

namespace App\Form\Admin;

use App\Entity\Technologie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TechnologyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Nom de la technologie',
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'label' => 'Description de la technologie',
            ])
            ->add('picture', FileType::class, [
                'required' => false,
                'data_class' => null,
                'label' => 'Image de présentation',
                'attr' => [
                    'data-controller' => 'preview',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Technologie::class,
        ]);
    }
}
