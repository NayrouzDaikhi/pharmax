<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Form\FormError;

class Produit1Type extends AbstractType
{
    public const ETAT_HORS_STOCK = 'hors_stock';
    public const ETAT_EN_STOCK = 'en_stock';
    public const ETAT_EN_PROMOTION = 'en_promotion';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('description')
            ->add('prix')
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité',
                'attr' => ['class' => 'form-control', 'min' => '0'],
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(['value' => 0]),
                ]
            ])
            ->add('image', FileType::class, [
                'label' => 'Choisir image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG, GIF, WebP)',
                    ])
                ],
            ])
            ->add('dateExpiration', DateType::class, [
                'label' => 'Date d\'expiration',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'attr' => ['class' => 'form-control', 'type' => 'date'],
            ])
            ->add('etatStock', ChoiceType::class, [
                'label' => 'État du Stock',
                'mapped' => false,
                'choices' => [
                    'En stock' => self::ETAT_EN_STOCK,
                    'En promotion' => self::ETAT_EN_PROMOTION,
                    'Hors Stock' => self::ETAT_HORS_STOCK,
                ],
                'attr' => ['class' => 'form-select', 'id' => 'produit_etatStock'],
            ])
            ->add('promotionPourcentage', IntegerType::class, [
                'label' => 'Pourcentage de promotion (%)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 1, 'max' => 100, 'placeholder' => 'Ex: 20', 'id' => 'produit_promotionPourcentage'],
                'constraints' => [
                    new Range(['min' => 1, 'max' => 100, 'notInRangeMessage' => 'Le pourcentage doit être entre 1 et 100']),
                ],
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'attr' => ['class' => 'form-select'],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $produit = $event->getData();
            $form = $event->getForm();
            if (!$produit instanceof Produit) {
                return;
            }
            $etat = self::ETAT_EN_STOCK;
            if ($produit->getPromotionPourcentage() !== null && $produit->getPromotionPourcentage() > 0) {
                $etat = self::ETAT_EN_PROMOTION;
            } elseif ($produit->isStatut() === false) {
                $etat = self::ETAT_HORS_STOCK;
            }
            $form->get('etatStock')->setData($etat);
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
            $data = $event->getData();
            $produit = $event->getForm()->getData();
            if (!$produit instanceof Produit) {
                return;
            }
            $etat = $data['etatStock'] ?? self::ETAT_EN_STOCK;
            $produit->setStatut($etat !== self::ETAT_HORS_STOCK);
            if ($etat !== self::ETAT_EN_PROMOTION) {
                $produit->setPromotionPourcentage(null);
            } else {
                $pct = isset($data['promotionPourcentage']) && $data['promotionPourcentage'] !== '' ? (int) $data['promotionPourcentage'] : null;
                $produit->setPromotionPourcentage($pct);
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            $form = $event->getForm();
            $etat = $form->get('etatStock')->getData();
            if ($etat === self::ETAT_EN_PROMOTION) {
                $pct = $form->get('promotionPourcentage')->getData();
                if ($pct === null || $pct < 1 || $pct > 100) {
                    $form->get('promotionPourcentage')->addError(new FormError('Veuillez saisir un pourcentage entre 1 et 100 pour la promotion.'));
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
