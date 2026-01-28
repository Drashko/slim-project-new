<?php

declare(strict_types=1);

namespace App\Web\Admin\Form;

use App\Web\Admin\Dto\AdminLoginFormData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AdminLoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'admin.login.email_label',
                'attr' => ['autocomplete' => 'email'],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'admin.login.password_label',
                'attr' => ['autocomplete' => 'current-password'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AdminLoginFormData::class,
            'csrf_protection' => true,
            'csrf_token_id' => 'admin_login',
            'method' => 'POST',
        ]);
    }
}
