<?php

namespace App\Form\Admin\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SecurityExtension extends AbstractTypeExtension
{
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!$options['is_granted_attribute']) {
            return;
        }

        if ($options['is_granted_hide']) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
                if ($this->isGranted($options)) {
                    return;
                }

                $form = $event->getForm();
                $form->getParent()->remove($form->getName());
            });
        } else {
            $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
                if ($this->isGranted($options)) {
                    return;
                }

                $event->setData($event->getForm()->getViewData());
            });
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'is_granted_attribute' => null,
            'is_granted_hide' => false,
        ]);
    }

    private function isGranted(array $options): bool
    {
        if (!$options['is_granted_attribute']) {
            return true;
        }

        if ($this->authorizationChecker->isGranted($options['is_granted_attribute'])) {
            return true;
        }

        return false;
    }
}
