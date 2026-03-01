<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('contenu')
            ->add('image', FileType::class, 
                [
                'label' => 'Image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                    ])
                ],
                ])
            ->add('saveDraft', SubmitType::class, [
                'label' => 'Enregistrer le brouillon',
                'attr' => [
                    'class' => 'btn btn-secondary',
                    'icon' => 'bx bx-save',
                ]
            ])
            ->add('publish', SubmitType::class, [
                'label' => 'Publier maintenant',
                'attr' => [
                    'class' => 'btn btn-primary',
                    'icon' => 'bx bx-upload',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
