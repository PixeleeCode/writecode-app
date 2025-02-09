<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
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
            ->add('password', RepeatedType::class, [
                'required' => true,
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passes ne correspondent pas',
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmer le mot de passe'],
            ])
            ->add('notifications', CheckboxType::class, [
                'required' => false,
                'label' => false,
            ])
            ->add('rgpd', CheckboxType::class, [
                'required' => true,
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => 'user:new',
        ]);
    }
}
