-- Create test user if it doesn't exist
INSERT INTO `user` (`email`, `roles`, `password`, `first_name`, `last_name`, `created_at`, `is_verified`)
SELECT 'test@example.com', '["ROLE_USER"]', '$2y$13$placeholder', 'Test', 'User', NOW(), 1
WHERE NOT EXISTS (SELECT 1 FROM `user` WHERE `email` = 'test@example.com');

-- Get the user ID
SET @user_id = (SELECT `id` FROM `user` WHERE `email` = 'test@example.com' LIMIT 1);

-- Create test commande (order)
INSERT INTO `commandes` (`utilisateur_id`, `produits`, `totales`, `statut`, `created_at`)
VALUES (
    @user_id,
    JSON_OBJECT(
        'product1', JSON_OBJECT('name', 'Test Product 1', 'price', 50.00, 'quantity', 2),
        'product2', JSON_OBJECT('name', 'Test Product 2', 'price', 30.00, 'quantity', 1)
    ),
    110.00,
    'en_attente',
    NOW()
);

-- Get the last inserted commande ID
SET @commande_id = LAST_INSERT_ID();

-- Create line items for the commande
INSERT INTO `ligne_commande` (`commande_id`, `nom`, `prix`, `quantite`, `sous_total`)
VALUES 
    (@commande_id, 'Test Product 1', 50.00, 2, 100.00),
    (@commande_id, 'Test Product 2', 30.00, 1, 30.00);

SELECT CONCAT('Test commande created with ID: ', @commande_id, ' for user: test@example.com') AS 'Result';
