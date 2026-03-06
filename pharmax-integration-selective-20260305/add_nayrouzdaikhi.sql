-- Add user nayrouzdaikhi@gmail.com
INSERT INTO `user` 
(email, password, first_name, last_name, roles, status, created_at, updated_at) 
VALUES 
('nayrouzdaikhi@gmail.com', 
 '$2y$13$ZxvKxFAFz3v6/m.K1q9cK.8xZ8D5.v3Z5v3Z5v3Z5v3Z5v3Z5v3Z5', 
 'Nayrouz', 
 'Daikhi',
 '["ROLE_USER"]',
 'active',
 NOW(),
 NOW());

-- Verify the user was added
SELECT id, email, first_name, last_name, status FROM `user` WHERE email = 'nayrouzdaikhi@gmail.com';
