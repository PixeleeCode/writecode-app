<?php

namespace App\Form\Account;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'required' => true,
                'label' => 'Prénom',
            ])
            ->add('lastname', TextType::class, [
                'required' => true,
                'label' => 'Nom',
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'Adresse email',
            ])
            ->add('roles', ChoiceType::class, [
                'required' => false,
                'label' => 'Rôle utilisateur',
                'is_granted_attribute' => 'ROLE_PREVIOUS_ADMIN',
                'is_granted_hide' => true,
                'multiple' => true,
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Modérateur' => 'ROLE_MODERATOR',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
            ])
            ->add('notifications', CheckboxType::class, [
                'required' => false,
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => 'user:edit',
        ]);
    }
}
