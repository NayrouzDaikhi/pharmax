<?php

namespace App\Command;

use App\Entity\Categorie;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-test-products',
    description: 'Create test products with expiration dates',
)]
class CreateTestProductsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Créer ou récupérer une catégorie
        $categorie = $this->em->getRepository(Categorie::class)->findOneBy(['nom' => 'Test']);
        
        if (!$categorie) {
            $categorie = new Categorie();
            $categorie->setNom('Test');
            $categorie->setDescription('Catégorie de test');
            $categorie->setCreatedAt(new \DateTime());
            $this->em->persist($categorie);
            $this->em->flush();
        }

        // Créer 3 produits de test avec dates d'expiration proches
        $products = [
            [
                'nom' => 'Produit Expirant Demain',
                'description' => 'Ce produit expire dans 1 jour',
                'prix' => 29.99,
                'dateExpiration' => (new \DateTime())->add(new \DateInterval('P1D')),
                'quantite' => 10,
            ],
            [
                'nom' => 'Produit Expirant dans 15 jours',
                'description' => 'Ce produit expire dans 15 jours',
                'prix' => 49.99,
                'dateExpiration' => (new \DateTime())->add(new \DateInterval('P15D')),
                'quantite' => 5,
            ],
            [
                'nom' => 'Produit Expirant dans 25 jours',
                'description' => 'Ce produit expire dans 25 jours',
                'prix' => 39.99,
                'dateExpiration' => (new \DateTime())->add(new \DateInterval('P25D')),
                'quantite' => 8,
            ],
        ];

        foreach ($products as $data) {
            $produit = new Produit();
            $produit->setNom($data['nom']);
            $produit->setDescription($data['description']);
            $produit->setPrix($data['prix']);
            $produit->setDateExpiration($data['dateExpiration']);
            $produit->setQuantite($data['quantite']);
            $produit->setStatut(true);
            $produit->setCategorie($categorie);

            $this->em->persist($produit);
            $io->info(sprintf('✅ Créé: %s (expire le %s)', $data['nom'], $data['dateExpiration']->format('Y-m-d')));
        }

        $this->em->flush();

        $io->success('3 produits de test créés avec succès!');

        return Command::SUCCESS;
    }
}
