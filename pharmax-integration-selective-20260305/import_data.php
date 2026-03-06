<?php
// Import data into Pharmax database
require_once __DIR__ . '/config/bootstrap.php';

use App\Entity\Categorie;
use App\Entity\Produit;
use App\Entity\Article;
use App\Entity\Reclamation;
use App\Entity\Reponse;
use App\Entity\Commentaire;
use Doctrine\ORM\EntityManagerInterface;

$container = require_once __DIR__.'/config/bootstrap.php';
$em = $container->get(EntityManagerInterface::class);

try {
    // Clear existing data
    $em->getConnection()->executeStatement('TRUNCATE TABLE commentaire');
    $em->getConnection()->executeStatement('TRUNCATE TABLE reponse');
    $em->getConnection()->executeStatement('TRUNCATE TABLE reclamation');
    $em->getConnection()->executeStatement('TRUNCATE TABLE article');
    $em->getConnection()->executeStatement('TRUNCATE TABLE produit');
    $em->getConnection()->executeStatement('TRUNCATE TABLE categorie');
    
    echo "[✓] Tables cleared\n";
    
    // Insert Categories
    $categories = [
        ['name' => 'Médicaments', 'desc' => 'Tous les médicaments disponibles en pharmacie', 'image' => 'medicaments.jpg'],
        ['name' => 'Vitamines & Suppléments', 'desc' => 'Vitamines et compléments alimentaires', 'image' => 'vitamines.jpg'],
        ['name' => 'Hygiène & Soins', 'desc' => 'Produits de hygiène personnelle et de soin', 'image' => 'hygiene.jpg'],
        ['name' => 'Dispositifs Médicaux', 'desc' => 'Équipements et dispositifs médicaux', 'image' => 'dispositifs.jpg'],
    ];
    
    $catIds = [];
    foreach ($categories as $cat) {
        $categorie = new Categorie();
        $categorie->setNom($cat['name']);
        $categorie->setDescription($cat['desc']);
        $categorie->setImage($cat['image']);
        $em->persist($categorie);
    }
    $em->flush();
    echo "[✓] 4 categories inserted\n";
    
    // Get category IDs
    $cats = $em->getRepository(Categorie::class)->findAll();
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
        $em->persist($produit);
    }
    $em->flush();
    echo "[✓] 8 products inserted\n";
    
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
        $em->persist($article);
    }
    $em->flush();
    echo "[✓] 3 articles inserted\n";
    
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
        $em->persist($reclamation);
        $reclIds[$idx] = $reclamation;
    }
    $em->flush();
    echo "[✓] 3 reclamations inserted\n";
    
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
        $em->persist($reponse);
    }
    $em->flush();
    echo "[✓] 2 responses inserted\n";
    
    // Insert Comments
    $products = $em->getRepository(Produit::class)->findAll();
    $prodMap = [];
    foreach ($products as $p) {
        $prodMap[$p->getNom()] = $p;
    }
    
    $comments = [
        ['prod' => 'Paracétamol 500mg', 'content' => 'Excellent produit! Très efficace contre les migraines. Je recommande fortement.'],
        ['prod' => 'Paracétamol 500mg', 'content' => 'Bon prix et livraison rapide. Satisfait de mon achat.'],
        ['prod' => 'Vitamine C 1000mg', 'content' => 'Les vitamines sont de bonne qualité. Je les prends depuis 2 semaines et je me sens mieux.'],
        ['prod' => 'Savon Antibactérien', 'content' => 'Savon très doux pour la peau. Parfait pour la famille. Achat récurrent.'],
    ];
    
    foreach ($comments as $comm) {
        $commentaire = new Commentaire();
        $commentaire->setContenu($comm['content']);
        $commentaire->setProduit($prodMap[$comm['prod']]);
        $em->persist($commentaire);
    }
    $em->flush();
    echo "[✓] 4 comments inserted\n";
    
    // Show statistics
    echo "\n=== IMPORT SUMMARY ===\n";
    $catCount = $em->getRepository(Categorie::class)->count([]);
    $prodCount = $em->getRepository(Produit::class)->count([]);
    $artCount = $em->getRepository(Article::class)->count([]);
    $reclCount = $em->getRepository(Reclamation::class)->count([]);
    $commCount = $em->getRepository(Commentaire::class)->count([]);
    
    echo "Categories: $catCount\n";
    echo "Products: $prodCount\n";
    echo "Articles: $artCount\n";
    echo "Reclamations: $reclCount\n";
    echo "Comments: $commCount\n";
    echo "\n✓ Data import completed successfully!\n";
    
} catch (Exception $e) {
    echo "[✗] Error: " . $e->getMessage() . "\n";
    exit(1);
}
