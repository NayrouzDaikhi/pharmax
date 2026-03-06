<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEditMode = isset($options['data']) && $options['data']->getId() !== null;
        $currentUser = $options['currentUser'] ?? null;
        $editingUser = $options['editingUser'] ?? null;
        $isSuperAdmin = $options['isSuperAdmin'] ?? false;

        // Build role choices: Super Admin can assign all roles, normal admin can only assign User and Admin
        $roleChoices = [
            'User' => 'ROLE_USER',
            'Admin' => 'ROLE_ADMIN',
        ];
        
        // Only super admin can see/assign super admin role
        if ($isSuperAdmin) {
            $roleChoices['Super Admin'] = 'ROLE_SUPER_ADMIN';
        }

        $builder
            ->add('firstName', TextType::class, [
                'label' => 'First Name',
                'constraints' => [
                    new NotBlank(['message' => 'First name is required']),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'First name must not exceed 255 characters',
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z\s\-\']+$/u',
                        'message' => 'First name can only contain letters, spaces, hyphens and apostrophes',
                    ]),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last Name',
                'constraints' => [
                    new NotBlank(['message' => 'Last name is required']),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Last name must not exceed 255 characters',
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z\s\-\']+$/u',
                        'message' => 'Last name can only contain letters, spaces, hyphens and apostrophes',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new NotBlank(['message' => 'Email is required']),
                    new Email(['message' => 'Invalid email format']),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Email must not exceed 255 characters',
                    ]),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'mapped' => false,
                'required' => !$isEditMode,
                'disabled' => $isEditMode && !$isSuperAdmin, // Only super admin can edit password
                'constraints' => !$isEditMode ? [
                    new NotBlank(['message' => 'Password is required']),
                    new Length([
                        'min' => 6,
                        'max' => 4096,
                        'minMessage' => 'Password must be at least {{ limit }} characters long',
                        'maxMessage' => 'Password must not exceed 4096 characters',
                    ]),
                ] : ($isSuperAdmin ? [
                    new Length([
                        'min' => 6,
                        'max' => 4096,
                        'minMessage' => 'Password must be at least {{ limit }} characters long',
                        'maxMessage' => 'Password must not exceed 4096 characters',
                    ]),
                ] : []),
                'attr' => [
                    'placeholder' => $isEditMode ? ($isSuperAdmin ? 'Leave blank to keep current password' : 'Password cannot be changed') : 'Enter password',
                    'data-help-text' => $isEditMode && !$isSuperAdmin ? 'Contact Super Admin to change password' : '',
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Roles',
                'multiple' => true,
                'expanded' => true,
                'choices' => $roleChoices,
                // Role field is enabled for all users - normal admins just have limited choices
                'required' => false,
            ])
            ->add('isBlocked', CheckboxType::class, [
                'label' => 'Blocked',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'currentUser' => null,
            'editingUser' => null,
            'isSuperAdmin' => false,
        ]);
    }
}
