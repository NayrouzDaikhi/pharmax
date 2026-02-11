<?php

namespace App\Form;

use App\Entity\Commande;
use App\Entity\User;
use App\Form\DataTransformer\CommaSeparatedToArrayTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Date (créée le)
            ->add('created_at', DateTimeType::class, [
                'label' => 'Date de création',
                'widget' => 'single_text',
                'required' => true,
            ])
            
            // Montant total
            ->add('totales', NumberType::class, [
                'label' => 'Montant total (TND)',
                'scale' => 2,
                'data_class' => null,
            ])
            
            // Statut
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'En attente' => 'en_attente',
                    'En cours' => 'en_cours',
                    'Payée' => 'payee',
                    'Annulée' => 'annule',
                ],
            ])
            
            // Utilisateur
            ->add('utilisateur', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'label' => 'Utilisateur',
                'required' => false,
                'placeholder' => 'Sélectionner un utilisateur',
            ])
            
            // Produits
            ->add('produits', TextType::class, [
                'label' => 'Produits',
                'help' => 'Entrez les produits séparés par des virgules. Ex: iPhone 15, MacBook Pro',
                'required' => false,
                'empty_data' => '',
                'attr' => [
                    'placeholder' => 'iPhone 15, MacBook Pro',
                    'rows' => 3,
                ],
            ])
        ;

        // Convert comma-separated string <-> array for the entity's json field
        if ($builder->has('produits')) {
            $builder->get('produits')->addModelTransformer(new CommaSeparatedToArrayTransformer());
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
