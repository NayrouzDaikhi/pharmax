<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
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
                'required' => false,
                'empty_data' => '',
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Last name must not exceed 255 characters',
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z\s\-\']*$/u',
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
            ->add('plainPassword', PasswordType::class, [
                'label' => 'New Password',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Password must be at least {{ limit }} characters long',
                        'max' => 4096,
                        'maxMessage' => 'Password must not exceed 4096 characters',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Leave blank to keep current password',
                ],
            ])
            ->add('avatar', FileType::class, [
                'label' => 'Avatar (JPG, PNG or GIF)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image (JPG, PNG or GIF)',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

