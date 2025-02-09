<?php

namespace App\Form\Admin;

use App\Entity\Training;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrainingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Titre de la formation',
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'label' => 'Description de la formation',
            ])
            ->add('infos', TextType::class, [
                'required' => false,
                'label' => 'Information importante',
            ])
            ->add('picture', FileType::class, [
                'required' => false,
                'data_class' => null,
                'label' => 'Image de présentation',
                'attr' => [
                    'data-controller' => 'preview',
                ],
            ])
            ->add('chapters', CollectionType::class, [
                'required' => true,
                'label' => false,
                'entry_type' => ChapterType::class,
                'entry_options' => [
                    'training' => $options['data'],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'is_granted_attribute' => 'ROLE_ADMIN',
                'is_granted_hide' => true,
            ])
            ->add('notifications', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Notifier les utilisateurs',
                'is_granted_attribute' => 'ROLE_ADMIN',
                'is_granted_hide' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Training::class,
        ]);
    }
}
