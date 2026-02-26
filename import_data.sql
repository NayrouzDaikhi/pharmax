-- ============================================
-- IMPORT DATA FOR PHARMAX
-- ============================================

-- Delete duplicates in categories first
DELETE FROM categorie WHERE nom NOT IN ('Medicaments', 'Vitamines & Supplements', 'Hygiene & Soins', 'Dispositifs Medicaux');

-- Insert clean categories (if not existing)
INSERT IGNORE INTO categorie (nom, description, image, created_at) VALUES
('Medicaments', 'Tous les medicaments disponibles en pharmacie', 'medicaments.jpg', '2025-01-15 10:00:00'),
('Vitamines & Supplements', 'Vitamines et complements alimentaires', 'vitamines.jpg', '2025-01-15 10:00:00'),
('Hygiene & Soins', 'Produits de hygiene personnelle et de soin', 'hygiene.jpg', '2025-01-15 10:00:00'),
('Dispositifs Medicaux', 'Equipements et dispositifs medicaux', 'dispositifs.jpg', '2025-01-15 10:00:00');

-- Insert articles
INSERT INTO article (titre, contenu, contenu_en, image, created_at, updated_at, likes) VALUES
('10 Conseils pour Renforcer votre Systeme Immunitaire', 
 'Votre systeme immunitaire est votre premiere ligne de defense contre les maladies. Voici 10 conseils pour le renforcer naturellement: 1. Dormez suffisamment (7-9 heures par nuit), 2. Mangez equilibre avec fruits et legumes, 3. Buvez beaucoup d eau (2-3 litres par jour), 4. Faites de l exercice regulierement, 5. Gerez le stress avec la meditation, 6. Evitez le tabac et l alcool, 7. Prenez des vitamines et mineraux, 8. Exposez-vous au soleil (vitamine D), 9. Lavez-vous les mains regulierement, 10. Consultez votre pharmacien.',
 '10 Tips to Boost Your Immune System. Your immune system is your first line of defense against disease. Here are 10 tips to strengthen it naturally: 1. Get enough sleep (7-9 hours per night), 2. Eat a balanced diet with fruits and vegetables, 3. Drink plenty of water (2-3 liters per day), 4. Exercise regularly, 5. Manage stress with meditation, 6. Avoid smoking and alcohol, 7. Take vitamins and minerals, 8. Get sun exposure (Vitamin D), 9. Wash your hands regularly, 10. Consult your pharmacist.',
 'immunite.jpg', '2025-10-15 14:30:00', '2025-10-15 14:30:00', 25);

INSERT INTO article (titre, contenu, contenu_en, image, created_at, updated_at, likes) VALUES
('Difference entre Medicament Generique et Original',
 'Beaucoup de patients se demandent s il existe une difference entre un medicament generique et un original. Les generiques contiennent les memes principes actifs que les medicaments originaux. La difference reside dans: le prix (beaucoup plus abordable), l emballage, et les excipients (substances inactives). La qualite et l efficacite sont garanties par les autorites reglementaires. Les generiques sont egalement efficaces que les medicaments originaux et sont une excellente option pour economiser de l argent.',
 'Difference Between Generic and Brand Name Medications. Many patients wonder if there is a difference between a generic and a brand name medication. Generics contain the same active ingredients as original medications. The differences are: price (much more affordable), packaging, and excipients (inactive substances). Quality and efficacy are guaranteed by regulatory authorities. Generics are just as effective as brand name medications.',
 'generique.jpg', '2025-09-20 11:15:00', '2025-09-20 11:15:00', 42);

INSERT INTO article (titre, contenu, contenu_en, image, created_at, updated_at, likes) VALUES
('Les Bienfaits de la Vitamine D en Hiver',
 'L hiver est la saison où nous produisons moins de vitamine D naturelle due au manque d exposition au soleil. La vitamine D est essentielle pour notre sante: elle permet d absorber le calcium (sante des os), de reguler le systeme immunitaire, d ameliorer l humeur et de prevenir la depression saisonniere, et de favoriser l absorption du phosphore. Pendant l hiver, il est recommande de prendre des supplements de vitamine D. Consultez votre pharmacien pour determiner la dose appropriee.',
 'Benefits of Vitamin D in Winter. Winter is when we produce less natural vitamin D due to lack of sun exposure. Vitamin D is essential for our health: it allows calcium absorption (bone health), regulates the immune system, improves mood and prevents seasonal depression, and promotes phosphorus absorption. During winter, it is recommended to take vitamin D supplements. Consult your pharmacist to determine the appropriate dose.',
 'vitamine-d.jpg', '2025-09-10 09:45:00', '2025-09-10 09:45:00', 18);

-- Insert products
INSERT INTO produit (nom, description, prix, image, date_expiration, statut, quantite, categorie_id, created_at) VALUES
('Paracetamol 500mg', 'Antidouleur et anti-fievre efficace pour les maux de tete, douleurs musculaires et la fievre', 5.99, 'paracetamol.jpg', '2026-12-31', 1, 150, 1, '2025-02-01 08:30:00'),
('Vitamine C 1000mg', 'Supplement de vitamine C pour renforcer le systeme immunitaire', 12.50, 'vitamine-c.jpg', '2027-06-30', 1, 85, 2, '2025-02-01 08:30:00'),
('Ibuprofe​ne 200mg', 'Anti-inflammatoire non steroidien pour soulager les douleurs', 8.75, 'ibuprofen.jpg', '2026-09-15', 1, 120, 1, '2025-02-01 08:30:00'),
('Savon Antibacterien', 'Savon antibacterien pour l hygiene quotidienne des mains', 3.45, 'savon.jpg', '2027-03-20', 1, 250, 3, '2025-02-01 08:30:00'),
('Thermometre Numerique', 'Thermometre electronique pour mesure precise de la temperature', 29.99, 'thermometre.jpg', '2028-12-31', 1, 45, 4, '2025-02-01 08:30:00'),
('Sirop Toux D Or', 'Sirop pour la toux seche et productive', 15.00, 'sirop-toux.jpg', '2026-08-10', 1, 60, 1, '2025-02-01 08:30:00'),
('Gel Antiseptique', 'Gel antibacterien pour les mains', 6.99, 'gel-antiseptique.jpg', '2026-11-25', 1, 200, 3, '2025-02-01 08:30:00'),
('Pansements Steriles', 'Boite de 50 pansements steriles assorties', 4.50, 'pansements.jpg', '2027-01-15', 1, 180, 4, '2025-02-01 08:30:00');

-- Insert comments 
INSERT INTO commentaire (produit_id, contenu, created_at) VALUES
(1, 'Excellent produit! Tres efficace contre les migraines. Je recommande fortement.', '2025-02-06 12:45:00'),
(1, 'Bon prix et livraison rapide. Satisfait de mon achat.', '2025-02-07 16:20:00'),
(2, 'Les vitamines sont de bonne qualite. Je les prends depuis 2 semaines et je me sens mieux.', '2025-02-08 18:30:00'),
(4, 'Savon tres doux pour la peau. Parfait pour la famille. Achat recurrent.', '2025-02-09 14:15:00');
