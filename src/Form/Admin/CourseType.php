<?php

namespace App\Form\Admin;

use App\Entity\Course;
use App\Entity\Technologie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'label' => 'Titre de l\'article',
            ])
            ->add('picture', FileType::class, [
                'required' => false,
                'data_class' => null,
                'label' => 'Image de présentation',
                'attr' => [
                    'data-controller' => 'preview',
                ],
            ])
            ->add('content', TextareaType::class, [
                'required' => true,
                'label' => 'Contenu de l\'article',
            ])
            ->add('draft', CheckboxType::class, [
                'required' => false,
                'label' => 'Brouillon',
                'is_granted_attribute' => 'ROLE_ADMIN',
                'is_granted_hide' => true,
            ])
            ->add('premium', CheckboxType::class, [
                'required' => false,
                'label' => 'Premium',
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
            ->add('update', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Notification de mise à jour ?',
                'is_granted_attribute' => 'ROLE_ADMIN',
                'is_granted_hide' => true,
            ])
            ->add('created_at', DateTimeType::class, [
                'required' => true,
                'label' => 'Date de publication',
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy HH:mm',
                'html5' => false,
            ])
            ->add('technology', EntityType::class, [
                'required' => true,
                'multiple' => true,
                'label' => 'Technologies utilisées',
                'class' => Technologie::class,
                'choice_label' => 'name',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
        ]);
    }
}
