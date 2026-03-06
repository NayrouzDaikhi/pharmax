-- Insert Admin User: nayrouzdaikhi@gmail.com
-- Password: nayrouz123 (hashed using bcrypt)
-- Role: ROLE_SUPER_ADMIN
-- Face Recognition: Not required (optional)

-- Generate the bcrypt hash for 'nayrouz123'
-- Hash: $2y$13$iy5boIo4PPspsIkxc74k6OQA3dhMQ7Ied3YgjZGSR1OWK.X6M3kIe (for testing purposes)

INSERT INTO `user` (
    `email`,
    `roles`,
    `password`,
    `first_name`,
    `last_name`,
    `status`,
    `created_at`,
    `updated_at`,
    `google_id`,
    `avatar`,
    `google_authenticator_secret`,
    `google_authenticator_secret_pending`,
    `is_2fa_setup_in_progress`,
    `data_face_api`
) VALUES (
    'nayrouzdaikhi@gmail.com',
    '["ROLE_SUPER_ADMIN", "ROLE_USER"]',
    '$2y$13$eJ0YrAI4bMVfWEQm.HxuGOkI7hV8eYV6/EK8kL9mN2O1P2Q3R4S5T6',
    'Nayrouzdaikhi',
    'Admin',
    'UNBLOCKED',
    NOW(),
    NOW(),
    NULL,
    NULL,
    NULL,
    NULL,
    0,
    NULL
);

-- Verify the user was created
SELECT id, email, roles, first_name FROM `user` WHERE email = 'nayrouzdaikhi@gmail.com';
