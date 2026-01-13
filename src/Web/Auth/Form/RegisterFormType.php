<?php

declare(strict_types=1);

namespace App\Web\Auth\Form;

use App\Web\Auth\Dto\RegisterFormData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RegisterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'auth.register.email_label',
                'attr' => ['autocomplete' => 'email'],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'auth.register.password_label',
                'attr' => ['autocomplete' => 'new-password'],
            ])
            ->add('confirmPassword', PasswordType::class, [
                'label' => 'auth.register.confirm_password_label',
                'attr' => ['autocomplete' => 'new-password'],
            ])
            ->add('accountType', ChoiceType::class, [
                'label' => 'auth.register.account_type.label',
                'choices' => RegisterFormData::ACCOUNT_TYPE_CHOICES,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RegisterFormData::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'register_form',
            'method' => 'POST',
        ]);
    }
}
