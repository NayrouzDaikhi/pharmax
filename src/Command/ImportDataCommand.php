<?php

namespace App\Command;

use App\Entity\Article;
use App\Entity\Categorie;
use App\Entity\Commentaire;
use App\Entity\Produit;
use App\Entity\Reclamation;
use App\Entity\Reponse;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:import-data',
    description: 'Import sample data to the Pharmax database',
)]
class ImportDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Starting data import...');

        try {
            // Disable foreign key constraints for truncation
            $this->em->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS=0');
            
            // Clear existing data
            $this->em->getConnection()->executeStatement('TRUNCATE TABLE commentaire');
            $this->em->getConnection()->executeStatement('TRUNCATE TABLE reponse');
            $this->em->getConnection()->executeStatement('TRUNCATE TABLE reclamation');
            $this->em->getConnection()->executeStatement('TRUNCATE TABLE article');
            $this->em->getConnection()->executeStatement('TRUNCATE TABLE produit');
            $this->em->getConnection()->executeStatement('TRUNCATE TABLE categorie');
            
            // Re-enable foreign key constraints
            $this->em->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS=1');
            
            $output->writeln('[✓] Tables cleared');

            // Insert Categories
            $categories = [
                ['name' => 'Médicaments', 'desc' => 'Tous les médicaments disponibles en pharmacie', 'image' => 'medicaments.jpg'],
                ['name' => 'Vitamines & Suppléments', 'desc' => 'Vitamines et compléments alimentaires', 'image' => 'vitamines.jpg'],
                ['name' => 'Hygiène & Soins', 'desc' => 'Produits de hygiène personnelle et de soin', 'image' => 'hygiene.jpg'],
                ['name' => 'Dispositifs Médicaux', 'desc' => 'Équipements et dispositifs médicaux', 'image' => 'dispositifs.jpg'],
            ];

            foreach ($categories as $cat) {
                $categorie = new Categorie();
                $categorie->setNom($cat['name']);
                $categorie->setDescription($cat['desc']);
                $categorie->setImage($cat['image']);
                $this->em->persist($categorie);
            }
            $this->em->flush();
            $output->writeln('[✓] 4 categories inserted');

            // Get category map
            $cats = $this->em->getRepository(Categorie::class)->findAll();
            $catMap = [];
            foreach ($cats as $cat) {
                $catMap[$cat->getNom()] = $cat;
            }

            // Insert Products
            $products = [
                ['nom' => 'Paracétamol 500mg', 'desc' => 'Antidouleur et anti-fièvre efficace pour les maux de tête, douleurs musculaires et la fièvre', 'prix' => 5.99, 'image' => 'paracetamol.jpg', 'exp' => '2026-12-31', 'qty' => 150, 'cat' => 'Médicaments'],
                ['nom' => 'Vitamine C 1000mg', 'desc' => 'Supplement de vitamine C pour renforcer le système immunitaire', 'prix' => 12.50, 'image' => 'vitamine-c.jpg', 'exp' => '2027-06-30', 'qty' => 85, 'cat' => 'Vitamines & Suppléments'],
                ['nom' => 'Ibuprofène 200mg', 'desc' => 'Anti-inflammatoire non stéroïdien pour soulager les douleurs', 'prix' => 8.75, 'image' => 'ibuprofen.jpg', 'exp' => '2026-09-15', 'qty' => 120, 'cat' => 'Médicaments'],
                ['nom' => 'Savon Antibactérien', 'desc' => 'Savon antibactérien pour l\'hygiène quotidienne des mains', 'prix' => 3.45, 'image' => 'savon.jpg', 'exp' => '2027-03-20', 'qty' => 250, 'cat' => 'Hygiène & Soins'],
                ['nom' => 'Thermomètre Numérique', 'desc' => 'Thermomètre électronique pour mesure précise de la température', 'prix' => 29.99, 'image' => 'thermometre.jpg', 'exp' => '2028-12-31', 'qty' => 45, 'cat' => 'Dispositifs Médicaux'],
                ['nom' => 'Sirop Toux D\'or', 'desc' => 'Sirop pour la toux sèche et productive', 'prix' => 15.00, 'image' => 'sirop-toux.jpg', 'exp' => '2026-08-10', 'qty' => 60, 'cat' => 'Médicaments'],
                ['nom' => 'Gel Antiseptique', 'desc' => 'Gel antibactérien pour les mains', 'prix' => 6.99, 'image' => 'gel-antiseptique.jpg', 'exp' => '2026-11-25', 'qty' => 200, 'cat' => 'Hygiène & Soins'],
                ['nom' => 'Pansements Stériles', 'desc' => 'Boîte de 50 pansements stériles assorties', 'prix' => 4.50, 'image' => 'pansements.jpg', 'exp' => '2027-01-15', 'qty' => 180, 'cat' => 'Dispositifs Médicaux'],
            ];

            foreach ($products as $prod) {
                $produit = new Produit();
                $produit->setNom($prod['nom']);
                $produit->setDescription($prod['desc']);
                $produit->setPrix($prod['prix']);
                $produit->setImage($prod['image']);
                $produit->setDateExpiration(new DateTime($prod['exp']));
                $produit->setStatut(true);
                $produit->setQuantite($prod['qty']);
                $produit->setCategorie($catMap[$prod['cat']]);
                $this->em->persist($produit);
            }
            $this->em->flush();
            $output->writeln('[✓] 8 products inserted');

            // Insert Articles
            $articles = [
                [
                    'titre' => '10 Conseils pour Renforcer votre Système Immunitaire',
                    'contenu' => 'Votre système immunitaire est votre première ligne de défense.

1. Dormez suffisamment (7-9 heures par nuit)
2. Mangez équilibré avec fruits et légumes
3. Buvez beaucoup d\'eau (2-3 litres par jour)
4. Faites de l\'exercice régulièrement
5. Gérez le stress avec la méditation
6. Évitez le tabac et l\'alcool
7. Prenez des vitamines et minéraux
8. Exposez-vous au soleil (vitamine D)
9. Lavez-vous les mains régulièrement
10. Consultez votre pharmacien',
                    'contenu_en' => '10 Tips to Boost Your Immune System

Your immune system is your first line of defense.

1. Get enough sleep (7-9 hours per night)
2. Eat a balanced diet with fruits and vegetables
3. Drink plenty of water (2-3 liters per day)
4. Exercise regularly
5. Manage stress with meditation
6. Avoid smoking and alcohol
7. Take vitamins and minerals
8. Get sun exposure (Vitamin D)
9. Wash your hands regularly
10. Consult your pharmacist',
                    'image' => 'immunite.jpg',
                    'likes' => 25,
                ],
                [
                    'titre' => 'Différence entre Médicament Générique et Original',
                    'contenu' => 'Beaucoup de patients se demandent s\'il existe une différence entre un médicament générique et un original.

Les génériques contiennent les mêmes principes actifs que les médicaments originaux. La différence réside dans:
- Le prix (beaucoup plus abordable)
- L\'emballage
- Les excipients (substances inactives)

La qualité et l\'efficacité sont garanties par les autorités réglementaires.',
                    'contenu_en' => 'Difference Between Generic and Brand Name Medications

Many patients wonder if there\'s a difference between a generic and a brand name medication.

Generics contain the same active ingredients as original medications. The differences are:
- Price (much more affordable)
- Packaging
- Excipients (inactive substances)

Quality and efficacy are guaranteed by regulatory authorities.',
                    'image' => 'generique.jpg',
                    'likes' => 42,
                ],
                [
                    'titre' => 'Les Bienfaits de la Vitamine D en Hiver',
                    'contenu' => 'L\'hiver est la saison où nous produisons moins de vitamine D naturelle.

La vitamine D est essentielle pour:
- Absorber le calcium (santé des os)
- Réguler le système immunitaire
- Améliorer l\'humeur et prévenir la dépression saisonnière
- Favoriser l\'absorption du phosphore

Consultez votre pharmacien pour les suppléments appropriés.',
                    'contenu_en' => 'Benefits of Vitamin D in Winter

Winter is when we produce less natural vitamin D.

Vitamin D is essential for:
- Calcium absorption (bone health)
- Regulating the immune system
- Improving mood and preventing seasonal depression
- Promoting phosphorus absorption

Consult your pharmacist for appropriate supplements.',
                    'image' => 'vitamine-d.jpg',
                    'likes' => 18,
                ],
            ];

            foreach ($articles as $art) {
                $article = new Article();
                $article->setTitre($art['titre']);
                $article->setContenu($art['contenu']);
                $article->setContenuEn($art['contenu_en']);
                $article->setImage($art['image']);
                $article->setLikes($art['likes']);
                $this->em->persist($article);
            }
            $this->em->flush();
            $output->writeln('[✓] 3 articles inserted');

            // Insert Reclamations
            $reclamations = [
                ['titre' => 'Produit endommagé à la réception', 'desc' => 'Reçu commande de vitamines C avec boîte endommagée et comprimés cassés. Demande de remboursement ou remplacement.', 'statut' => 'Resolu'],
                ['titre' => 'Délai de livraison trop long', 'desc' => 'Commandé un thermomètre numérique depuis 5 jours, pas encore reçu. Le site indiquait 2-3 jours de livraison.', 'statut' => 'En cours'],
                ['titre' => 'Produit non conforme', 'desc' => 'Le gel antibactérien reçu a une odeur différente. Je doute de son authenticité.', 'statut' => 'En attente'],
            ];

            $reclIds = [];
            foreach ($reclamations as $idx => $recl) {
                $reclamation = new Reclamation();
                $reclamation->setTitre($recl['titre']);
                $reclamation->setDescription($recl['desc']);
                $reclamation->setStatut($recl['statut']);
                $this->em->persist($reclamation);
                $reclIds[$idx] = $reclamation;
            }
            $this->em->flush();
            $output->writeln('[✓] 3 reclamations inserted');

            // Insert Responses
            $responses = [
                ['content' => 'Excusez le problème. Remboursement complet 12.50 DTN effectué. Remplacement gratuit en cours.

Cordialement,
Équipe Pharmax', 'recl_idx' => 0],
                ['content' => 'Colis en transit. Numéro de suivi: TRK-2025-0001234

Livraison prévue: 13 février 2025

Bien à vous,
Équipe Pharmax', 'recl_idx' => 1],
            ];

            foreach ($responses as $resp) {
                $reponse = new Reponse();
                $reponse->setContenu($resp['content']);
                $reponse->setReclamation($reclIds[$resp['recl_idx']]);
                $this->em->persist($reponse);
            }
            $this->em->flush();
            $output->writeln('[✓] 2 responses inserted');

            // Insert Comments using raw SQL to handle NULL values
            $comments = [
                ['nom' => 'Paracétamol 500mg', 'content' => 'Excellent produit! Très efficace contre les migraines. Je recommande fortement.'],
                ['nom' => 'Paracétamol 500mg', 'content' => 'Bon prix et livraison rapide. Satisfait de mon achat.'],
                ['nom' => 'Vitamine C 1000mg', 'content' => 'Les vitamines sont de bonne qualité. Je les prends depuis 2 semaines et je me sens mieux.'],
                ['nom' => 'Savon Antibactérien', 'content' => 'Savon très doux pour la peau. Parfait pour la famille. Achat récurrent.'],
            ];

            foreach ($comments as $comm) {
                $produit = $this->em->getRepository(Produit::class)->findOneBy(['nom' => $comm['nom']]);
                if ($produit) {
                    $sql = "INSERT INTO commentaire (produit_id, article_id, contenu, created_at, statut) 
                            VALUES (?, NULL, ?, NOW(), 'valide')";
                    $this->em->getConnection()->executeStatement($sql, [$produit->getId(), $comm['content']]);
                }
            }
            $output->writeln('[✓] 4 comments inserted');

            // Show summary
            $output->writeln('');
            $output->writeln('=== IMPORT SUMMARY ===');
            $output->writeln('Categories: 4');
            $output->writeln('Products: 8');
            $output->writeln('Articles: 3');
            $output->writeln('Reclamations: 3');
            $output->writeln('Comments: 4');
            $output->writeln('');
            $output->writeln('<info>✓ Data import completed successfully!</info>');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $output->writeln('<error>✗ Error: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
