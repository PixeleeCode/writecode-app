<?php

namespace App\Form\Account;

use App\Entity\Other\UserPassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserPasswordFormType extends AbstractType
{
    private TokenStorageInterface $token;

    public function __construct(TokenStorageInterface $token)
    {
        $this->token = $token;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('password', RepeatedType::class, [
                'required' => true,
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passes ne correspondent pas',
                'first_options' => ['label' => 'Nouveau mot de passe'],
                'second_options' => ['label' => 'Confirmer le nouveau mot de passe'],
            ])
        ;

        if ($this->token->getToken()->getUser()->getPassword()) {
            $builder->add('oldPassword', PasswordType::class, [
                'required' => true,
                'label' => 'Mot de passe actuel',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $validation = $this->token->getToken()->getUser()->getPassword() ? 'user:edit:password' : 'user:edit:password-social';
        $resolver->setDefaults([
            'data_class' => UserPassword::class,
            'validation_groups' => $validation,
        ]);
    }
}
