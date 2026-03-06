#Assuming MariaDB is running on localhost:3306

# First, add the missing column to the user table
# This assumes you have MySQL client installed or can paste this in phpMyAdmin

# If command line: mysql -u root -p pharmax -e "your_sql_here"
# Or in phpMyAdmin, run:

ALTER TABLE `user` ADD COLUMN IF NOT EXISTS `data_face_api` LONGTEXT DEFAULT NULL;

# Then, insert the new admin user:
INSERT INTO `user` (`email`, `roles`, `password`, `first_name`, `last_name`, `status`, `created_at`, `updated_at`, `data_face_api`) 
VALUES 
('nayrouzdaikhi@gmail.com', '["ROLE_SUPER_ADMIN", "ROLE_USER"]', '$2y$13$eJ0YrAI4bMVfWEQm.HxuGOkI7hV8eYV6/EK8kL9mN2O1P2Q3R4S5T6', 'Nayrouzdaikhi', 'Admin', 'UNBLOCKED', NOW(), NOW(), NULL);

# Verify the user was created:
SELECT id, email, first_name, last_name, roles FROM `user` WHERE email = 'nayrouzdaikhi@gmail.com';
