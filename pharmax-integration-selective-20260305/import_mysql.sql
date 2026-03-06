-- ============================================
-- PHARMAX MYSQL DATA IMPORT
-- Clean & proper MySQL format
-- ============================================

-- Clear existing data to avoid duplicates
TRUNCATE TABLE commentaire;
TRUNCATE TABLE ligne_commandes;
TRUNCATE TABLE commandes;
TRUNCATE TABLE reponse;
TRUNCATE TABLE reclamation;
TRUNCATE TABLE article;
TRUNCATE TABLE produit;
TRUNCATE TABLE categorie;

-- ============================================
-- INSERT CATEGORIES
-- ============================================
INSERT INTO categorie (nom, description, image, created_at) VALUES
('Médicaments', 'Tous les médicaments disponibles en pharmacie', 'medicaments.jpg', '2025-01-15 10:00:00'),
('Vitamines & Suppléments', 'Vitamines et compléments alimentaires', 'vitamines.jpg', '2025-01-15 10:00:00'),
('Hygiène & Soins', 'Produits de hygiène personnelle et de soin', 'hygiene.jpg', '2025-01-15 10:00:00'),
('Dispositifs Médicaux', 'Équipements et dispositifs médicaux', 'dispositifs.jpg', '2025-01-15 10:00:00');

-- ============================================
-- INSERT PRODUCTS
-- ============================================
INSERT INTO produit (nom, description, prix, image, date_expiration, statut, quantite, categorie_id, created_at) VALUES
('Paracétamol 500mg', 'Antidouleur et anti-fièvre efficace pour les maux de tête, douleurs musculaires et la fièvre', 5.99, 'paracetamol.jpg', '2026-12-31', 1, 150, 1, '2025-02-01 08:30:00'),
('Vitamine C 1000mg', 'Supplement de vitamine C pour renforcer le système immunitaire', 12.50, 'vitamine-c.jpg', '2027-06-30', 1, 85, 2, '2025-02-01 08:30:00'),
('Ibuprofène 200mg', 'Anti-inflammatoire non stéroïdien pour soulager les douleurs', 8.75, 'ibuprofen.jpg', '2026-09-15', 1, 120, 1, '2025-02-01 08:30:00'),
('Savon Antibactérien', 'Savon antibactérien pour l''hygiène quotidienne des mains', 3.45, 'savon.jpg', '2027-03-20', 1, 250, 3, '2025-02-01 08:30:00'),
('Thermomètre Numérique', 'Thermomètre électronique pour mesure précise de la température', 29.99, 'thermometre.jpg', '2028-12-31', 1, 45, 4, '2025-02-01 08:30:00'),
('Sirop Toux D''or', 'Sirop pour la toux sèche et productive', 15.00, 'sirop-toux.jpg', '2026-08-10', 1, 60, 1, '2025-02-01 08:30:00'),
('Gel Antiseptique', 'Gel antibactérien pour les mains', 6.99, 'gel-antiseptique.jpg', '2026-11-25', 1, 200, 3, '2025-02-01 08:30:00'),
('Pansements Stériles', 'Boîte de 50 pansements stériles assorties', 4.50, 'pansements.jpg', '2027-01-15', 1, 180, 4, '2025-02-01 08:30:00');

-- ============================================
-- INSERT ARTICLES
-- ============================================
INSERT INTO article (titre, contenu, contenu_en, image, created_at, likes) VALUES
('10 Conseils pour Renforcer votre Système Immunitaire', 
 'Votre système immunitaire est votre première ligne de défense.\n\n1. Dormez suffisamment (7-9 heures par nuit)\n2. Mangez équilibré avec fruits et légumes\n3. Buvez beaucoup d''eau (2-3 litres par jour)\n4. Faites de l''exercice régulièrement\n5. Gérez le stress avec la méditation\n6. Évitez le tabac et l''alcool\n7. Prenez des vitamines et minéraux\n8. Exposez-vous au soleil (vitamine D)\n9. Lavez-vous les mains régulièrement\n10. Consultez votre pharmacien',
 '10 Tips to Boost Your Immune System\n\nYour immune system is your first line of defense.\n\n1. Get enough sleep (7-9 hours per night)\n2. Eat a balanced diet with fruits and vegetables\n3. Drink plenty of water (2-3 liters per day)\n4. Exercise regularly\n5. Manage stress with meditation\n6. Avoid smoking and alcohol\n7. Take vitamins and minerals\n8. Get sun exposure (Vitamin D)\n9. Wash your hands regularly\n10. Consult your pharmacist',
 'immunite.jpg', '2025-10-15 14:30:00', 25),

('Différence entre Médicament Générique et Original',
 'Beaucoup de patients se demandent s''il existe une différence entre un médicament générique et un original.\n\nLes génériques contiennent les mêmes principes actifs que les médicaments originaux. La différence réside dans:\n- Le prix (beaucoup plus abordable)\n- L''emballage\n- Les excipients (substances inactives)\n\nLa qualité et l''efficacité sont garanties par les autorités réglementaires.',
 'Difference Between Generic and Brand Name Medications\n\nMany patients wonder if there''s a difference between a generic and a brand name medication.\n\nGenerics contain the same active ingredients as original medications. The differences are:\n- Price (much more affordable)\n- Packaging\n- Excipients (inactive substances)\n\nQuality and efficacy are guaranteed by regulatory authorities.',
 'generique.jpg', '2025-09-20 11:15:00', 42),

('Les Bienfaits de la Vitamine D en Hiver',
 'L''hiver est la saison où nous produisons moins de vitamine D naturelle.\n\nLa vitamine D est essentielle pour:\n- Absorber le calcium (santé des os)\n- Réguler le système immunitaire\n- Améliorer l''humeur et prévenir la dépression saisonnière\n- Favoriser l''absorption du phosphore\n\nConsultez votre pharmacien pour les suppléments appropriés.',
 'Benefits of Vitamin D in Winter\n\nWinter is when we produce less natural vitamin D.\n\nVitamin D is essential for:\n- Calcium absorption (bone health)\n- Regulating the immune system\n- Improving mood and preventing seasonal depression\n- Promoting phosphorus absorption\n\nConsult your pharmacist for appropriate supplements.',
 'vitamine-d.jpg', '2025-09-10 09:45:00', 18);

-- ============================================
-- INSERT RECLAMATIONS
-- ============================================
INSERT INTO reclamation (titre, description, date_creation, statut) VALUES
('Produit endommagé à la réception', 'Reçu commande de vitamines C avec boîte endommagée et comprimés cassés. Demande de remboursement ou remplacement.', '2025-02-08 09:15:00', 'Resolu'),
('Délai de livraison trop long', 'Commandé un thermomètre numérique depuis 5 jours, pas encore reçu. Le site indiquait 2-3 jours de livraison.', '2025-02-10 15:40:00', 'En cours'),
('Produit non conforme', 'Le gel antibactérien reçu a une odeur différente. Je doute de son authenticité.', '2025-02-11 11:20:00', 'En attente');

-- ============================================
-- INSERT RESPONSES
-- ============================================
INSERT INTO reponse (contenu, date_reponse, reclamation_id) VALUES
('Excusez le problème. Remboursement complet 12.50 DTN effectué. Remplacement gratuit en cours.\n\nCordialement,\nÉquipe Pharmax', '2025-02-09 10:30:00', 1),
('Colis en transit. Numéro de suivi: TRK-2025-0001234\n\nLivraison prévue: 13 février 2025\n\nBien à vous,\nÉquipe Pharmax', '2025-02-11 09:00:00', 2);

-- ============================================
-- INSERT COMMENTS
-- ============================================
INSERT INTO commentaire (produit_id, contenu, date_creation) VALUES
(1, 'Excellent produit! Très efficace contre les migraines. Je recommande fortement.', '2025-02-06 12:45:00'),
(1, 'Bon prix et livraison rapide. Satisfait de mon achat.', '2025-02-07 16:20:00'),
(2, 'Les vitamines sont de bonne qualité. Je les prends depuis 2 semaines et je me sens mieux.', '2025-02-08 18:30:00'),
(4, 'Savon très doux pour la peau. Parfait pour la famille. Achat récurrent.', '2025-02-09 14:15:00');

-- ============================================
-- VERIFICATION QUERIES
-- ============================================
SELECT 'Data Import Complete!' as Status;
SELECT COUNT(*) as Categories FROM categorie;
SELECT COUNT(*) as Products FROM produit;
SELECT COUNT(*) as Articles FROM article;
SELECT COUNT(*) as Reclamations FROM reclamation;
SELECT COUNT(*) as Comments FROM commentaire;
