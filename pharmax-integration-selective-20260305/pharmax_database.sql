-- ============================================
-- PHARMAX - DATABASE STRUCTURE & SAMPLE DATA
-- Database: Pharmax
-- Date: February 13, 2026
-- ============================================

-- ============================================
-- 1. CATEGORIES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS categorie (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 2. PRODUCTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS produit (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    prix FLOAT NOT NULL CHECK(prix > 0),
    image VARCHAR(255),
    date_expiration DATE NOT NULL,
    statut BOOLEAN NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    quantite INTEGER NOT NULL DEFAULT 0 CHECK(quantite >= 0),
    categorie_id INTEGER,
    FOREIGN KEY (categorie_id) REFERENCES categorie(id) ON DELETE SET NULL
);

-- ============================================
-- 3. ARTICLES TABLE (Blog)
-- ============================================
CREATE TABLE IF NOT EXISTS article (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    titre VARCHAR(255) NOT NULL,
    contenu TEXT NOT NULL,
    contenu_en TEXT,
    image VARCHAR(255),
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME,
    likes INTEGER DEFAULT 0
);

-- ============================================
-- 4. USERS TABLE (For orders)
-- ============================================
CREATE TABLE IF NOT EXISTS user (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    roles JSON,
    password VARCHAR(255) NOT NULL,
    nom VARCHAR(255),
    prenom VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 5. ORDERS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS commandes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    produits JSON,
    totales FLOAT NOT NULL,
    statut VARCHAR(50) DEFAULT 'en_attente',
    utilisateur_id INTEGER,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES user(id) ON DELETE SET NULL
);

-- ============================================
-- 6. ORDER LINES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS ligne_commandes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    commande_id INTEGER NOT NULL,
    nom VARCHAR(255) NOT NULL,
    prix FLOAT NOT NULL,
    quantite INTEGER NOT NULL,
    sous_total FLOAT NOT NULL,
    FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE
);

-- ============================================
-- 7. RECLAMATIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS reclamation (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    titre VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    statut VARCHAR(50) DEFAULT 'En attente'
);

-- ============================================
-- 8. RESPONSES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS reponse (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    contenu TEXT NOT NULL,
    date_reponse DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reclamation_id INTEGER NOT NULL,
    FOREIGN KEY (reclamation_id) REFERENCES reclamation(id) ON DELETE CASCADE
);

-- ============================================
-- 9. COMMENTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS commentaire (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    produit_id INTEGER,
    article_id INTEGER,
    contenu TEXT NOT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produit_id) REFERENCES produit(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES article(id) ON DELETE CASCADE
);

-- ============================================
-- SAMPLE DATA - CATEGORIES
-- ============================================
INSERT INTO categorie (nom, description, image, created_at) VALUES
('Médicaments', 'Tous les médicaments disponibles en pharmacie', 'medicaments.jpg', '2025-01-15 10:00:00'),
('Vitamines & Suppléments', 'Vitamines et compléments alimentaires', 'vitamines.jpg', '2025-01-15 10:00:00'),
('Hygiène & Soins', 'Produits de hygiène personnelle et de soin', 'hygiene.jpg', '2025-01-15 10:00:00'),
('Dispositifs Médicaux', 'Équipements et dispositifs médicaux', 'dispositifs.jpg', '2025-01-15 10:00:00');

-- ============================================
-- SAMPLE DATA - PRODUCTS
-- ============================================
INSERT INTO produit (nom, description, prix, image, date_expiration, statut, quantite, categorie_id, created_at) VALUES
('Paracétamol 500mg', 'Antidouleur et anti-fièvre efficace pour les maux de tête, douleurs musculaires et la fièvre', 5.99, 'paracetamol.jpg', '2026-12-31', 1, 150, 1, '2025-02-01 08:30:00'),
('Vitamine C 1000mg', 'Supplement de vitamine C pour renforcer le système immunitaire', 12.50, 'vitamine-c.jpg', '2027-06-30', 1, 85, 2, '2025-02-01 08:30:00'),
('Ibuprofène 200mg', 'Anti-inflammatoire non stéroïdien pour soulager les douleurs', 8.75, 'ibuprofen.jpg', '2026-09-15', 1, 120, 1, '2025-02-01 08:30:00'),
('Savon Antibactérien', 'Savon antibactérien pour l\'hygiène quotidienne des mains', 3.45, 'savon.jpg', '2027-03-20', 1, 250, 3, '2025-02-01 08:30:00'),
('Thermomètre Numérique', 'Thermomètre électronique pour mesure précise de la température', 29.99, 'thermometre.jpg', '2028-12-31', 1, 45, 4, '2025-02-01 08:30:00'),
('Sirop Toux D\'or', 'Sirop pour la toux sèche et productive', 15.00, 'sirop-toux.jpg', '2026-08-10', 1, 60, 1, '2025-02-01 08:30:00'),
('Gel Antiseptique', 'Gel antibactérien pour les mains', 6.99, 'gel-antiseptique.jpg', '2026-11-25', 1, 200, 3, '2025-02-01 08:30:00'),
('Pansements Stériles', 'Boîte de 50 pansements stériles assorties', 4.50, 'pansements.jpg', '2027-01-15', 1, 180, 4, '2025-02-01 08:30:00');

-- ============================================
-- SAMPLE DATA - ARTICLES (Blog)
-- ============================================
INSERT INTO article (titre, contenu, contenu_en, image, date_creation, likes) VALUES
('10 Conseils pour Renforcer votre Système Immunitaire', 
 'Votre système immunitaire est votre première ligne de défense...\n\n1. Dormez suffisamment (7-9 heures par nuit)\n2. Mangez équilibré avec fruits et légumes\n3. Buvez beaucoup d''eau (2-3 litres par jour)\n4. Faites de l''exercice régulièrement\n5. Gérez le stress avec la méditation\n6. Évitez le tabac et l''alcool\n7. Prenez des vitamines et minéraux\n8. Exposez-vous au soleil (vitamine D)\n9. Lavez-vous les mains régulièrement\n10. Consultez votre pharmacien',
 '10 Tips to Boost Your Immune System\n\nYour immune system is your first line of defense...\n\n1. Get enough sleep (7-9 hours per night)\n2. Eat a balanced diet with fruits and vegetables\n3. Drink plenty of water (2-3 liters per day)\n4. Exercise regularly\n5. Manage stress with meditation\n6. Avoid smoking and alcohol\n7. Take vitamins and minerals\n8. Get sun exposure (Vitamin D)\n9. Wash your hands regularly\n10. Consult your pharmacist',
 'immunite.jpg', '2025-10-15 14:30:00', 25),

('Différence entre Médicament Générique et Original',
 'Beaucoup de patients se demandent s''il existe une différence entre un médicament générique et un original...\n\nLes génériques contiennent les mêmes principes actifs que les médicaments originaux. La différence réside dans:\n- Le prix (beaucoup plus abordable)\n- L''emballage\n- Les excipients (substances inactives)\n\nLa qualité et l''efficacité sont garanties par les autorités réglementaires.',
 'Difference Between Generic and Brand Name Medications\n\nMany patients wonder if there''s a difference between a generic and a brand name medication...\n\nGenerics contain the same active ingredients as original medications. The differences are:\n- Price (much more affordable)\n- Packaging\n- Excipients (inactive substances)\n\nQuality and efficacy are guaranteed by regulatory authorities.',
 'generique.jpg', '2025-09-20 11:15:00', 42),

('Les Bienfaits de la Vitamine D en Hiver',
 'L''hiver est la saison où nous produisons moins de vitamine D naturelle...\n\nLa vitamine D est essentielle pour:\n- Absorber le calcium (santé des os)\n- Réguler le système immunitaire\n- Améliorer l''humeur et prévenir la dépression saisonnière\n- Favoriser l''absorption du phosphore\n\nConsultez votre pharmacien pour les suppléments appropriés.',
 'Benefits of Vitamin D in Winter\n\nWinter is when we produce less natural vitamin D...\n\nVitamin D is essential for:\n- Calcium absorption (bone health)\n- Regulating the immune system\n- Improving mood and preventing seasonal depression\n- Promoting phosphorus absorption\n\nConsult your pharmacist for appropriate supplements.',
 'vitamine-d.jpg', '2025-09-10 09:45:00', 18);

-- ============================================
-- SAMPLE DATA - USERS
-- ============================================
INSERT INTO user (email, nom, prenom, password, roles, created_at) VALUES
('patient1@pharmax.com', 'Dupont', 'Jean', '$2y$13$...', '["ROLE_USER"]', '2025-08-01 10:00:00'),
('patient2@pharmax.com', 'Martin', 'Marie', '$2y$13$...', '["ROLE_USER"]', '2025-08-15 14:30:00'),
('admin@pharmax.com', 'Admin', 'Pharmax', '$2y$13$...', '["ROLE_ADMIN"]', '2025-01-01 00:00:00');

-- ============================================
-- SAMPLE DATA - ORDERS (COMMANDES)
-- ============================================
INSERT INTO commandes (produits, totales, statut, utilisateur_id, created_at) VALUES
('[{"id": 1, "nom": "Paracétamol 500mg", "prix": 5.99}]', 5.99, 'Livrée', 1, '2025-02-05 10:00:00'),
('[{"id": 2, "nom": "Vitamine C 1000mg", "prix": 12.50}, {"id": 4, "nom": "Savon Antibactérien", "prix": 3.45}]', 15.95, 'En cours', 2, '2025-02-10 14:00:00'),
('[{"id": 5, "nom": "Thermomètre Numérique", "prix": 29.99}]', 29.99, 'En attente', 1, '2025-02-12 08:30:00');

-- ============================================
-- SAMPLE DATA - ORDER LINES (LIGNES COMMANDES)
-- ============================================
INSERT INTO ligne_commandes (commande_id, nom, prix, quantite, sous_total) VALUES
(1, 'Paracétamol 500mg', 5.99, 1, 5.99),
(2, 'Vitamine C 1000mg', 12.50, 1, 12.50),
(2, 'Savon Antibactérien', 3.45, 1, 3.45),
(3, 'Thermomètre Numérique', 29.99, 1, 29.99);

-- ============================================
-- SAMPLE DATA - RECLAMATIONS
-- ============================================
INSERT INTO reclamation (titre, description, date_creation, statut) VALUES
('Produit endommagé à la réception', 'J''ai reçu mon commande de vitamines C mais la boîte était endommagée et certains comprimés étaient cassés. Demande de remboursement ou remplacement.', '2025-02-08 09:15:00', 'Resolu'),
('Délai de livraison trop long', 'J''ai commandé un thermomètre numérique il y a 5 jours mais je ne l''ai pas encore reçu. Le site indiquait 2-3 jours de livraison.', '2025-02-10 15:40:00', 'En cours'),
('Produit non conforme', 'Le gel antibactérien que j''ai reçu a une odeur différente du gel habituel. Je doute de son authenticité.', '2025-02-11 11:20:00', 'En attente');

-- ============================================
-- SAMPLE DATA - RESPONSES (REPONSES)
-- ============================================
INSERT INTO reponse (contenu, date_reponse, reclamation_id) VALUES
('Bonjour,\n\nNous nous excusons pour ce problème. Nous avons procédé à un remboursement complet de 12.50 DTN sur votre compte. Le remboursement prendra 3-5 jours pour apparaître.\n\nDe plus, nous vous enverrons une nouvelle boîte en remplacement gratuitement.\n\nCordialement,\nÉquipe Pharmax', '2025-02-09 10:30:00', 1),

('Merci de votre patience,\n\nVotre colis est actuellement en transit. Le numéro de suivi est: TRK-2025-0001234\n\nVous pouvez suivre votre livraison ici: www.pharmax.com/track\n\nLivraison prévue: 13 février 2025\n\nBien à vous,\nÉquipe Pharmax', '2025-02-11 09:00:00', 2);

-- ============================================
-- SAMPLE DATA - COMMENTS
-- ============================================
INSERT INTO commentaire (produit_id, contenu, date_creation) VALUES
(1, 'Excellent produit! Très efficace contre les migraines. Je recommande fortement.', '2025-02-06 12:45:00'),
(1, 'Bon prix et livraison rapide. Satisfait de mon achat.', '2025-02-07 16:20:00'),
(2, 'Les vitamines sont de bonne qualité. Je les prends depuis 2 semaines et je me sens mieux.', '2025-02-08 18:30:00'),
(4, 'Savon très doux pour la peau. Parfait pour la famille. Achat récurrent.', '2025-02-09 14:15:00');

-- ============================================
-- SELECT QUERIES / AFFICHAGE DES DONNÉES
-- ============================================

-- 1. AFFICHER TOUTES LES CATEGORIES
-- SELECT * FROM categorie;

-- 2. AFFICHER TOUS LES PRODUITS AVEC CATEGORIE
-- SELECT p.id, p.nom, p.description, p.prix, p.quantite, p.image, c.nom as categorie 
-- FROM produit p 
-- LEFT JOIN categorie c ON p.categorie_id = c.id;

-- 3. AFFICHER TOUS LES ARTICLES
-- SELECT * FROM article ORDER BY date_creation DESC;

-- 4. AFFICHER TOUTES LES COMMANDES AVEC DETAILS UTILISATEUR
-- SELECT c.id, c.totales, c.statut, c.created_at, u.email, u.nom, u.prenom
-- FROM commandes c
-- LEFT JOIN user u ON c.utilisateur_id = u.id
-- ORDER BY c.created_at DESC;

-- 5. AFFICHER LES LIGNES D'UNE COMMANDE (Exemple: commande 1)
-- SELECT * FROM ligne_commandes WHERE commande_id = 1;

-- 6. AFFICHER TOUTES LES RECLAMATIONS
-- SELECT * FROM reclamation ORDER BY date_creation DESC;

-- 7. AFFICHER UNE RECLAMATION AVEC REPONSES
-- SELECT r.id, r.titre, r.description, r.statut, r.date_creation,
--        rep.id as reponse_id, rep.contenu, rep.date_reponse
-- FROM reclamation r
-- LEFT JOIN reponse rep ON r.id = rep.reclamation_id
-- WHERE r.id = 1
-- ORDER BY rep.date_reponse DESC;

-- 8. AFFICHER LES COMMENTAIRES D'UN PRODUIT (Exemple: produit 1)
-- SELECT * FROM commentaire WHERE produit_id = 1 ORDER BY date_creation DESC;

-- 9. AFFICHER LES PRODUITS EN STOCK BAS (moins de 50 unités)
-- SELECT id, nom, quantite FROM produit WHERE quantite < 50 ORDER BY quantite ASC;

-- 10. STATISTIQUES DE VENTES
-- SELECT COUNT(*) as nombre_commandes, SUM(totales) as total_ventes 
-- FROM commandes 
-- WHERE statut = 'Livrée';

-- ============================================
-- INDEXES FOR PERFORMANCE
-- ============================================
CREATE INDEX IF NOT EXISTS idx_produit_categorie ON produit(categorie_id);
CREATE INDEX IF NOT EXISTS idx_commandes_utilisateur ON commandes(utilisateur_id);
CREATE INDEX IF NOT EXISTS idx_ligne_commande_commande ON ligne_commandes(commande_id);
CREATE INDEX IF NOT EXISTS idx_reponse_reclamation ON reponse(reclamation_id);
CREATE INDEX IF NOT EXISTS idx_commentaire_produit ON commentaire(produit_id);
CREATE INDEX IF NOT EXISTS idx_commentaire_article ON commentaire(article_id);
CREATE INDEX IF NOT EXISTS idx_categorie_nom ON categorie(nom);
CREATE INDEX IF NOT EXISTS idx_produit_nom ON produit(nom);

-- ============================================
-- END OF DATABASE STRUCTURE & SAMPLE DATA
-- ============================================
