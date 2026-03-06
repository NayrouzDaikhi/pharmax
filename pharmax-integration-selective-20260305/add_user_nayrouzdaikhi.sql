-- Ajouter l'utilisateur nayrouzdaikhi@gmail.com
INSERT INTO user (
    email,
    password,
    roles,
    first_name,
    last_name,
    status,
    created_at,
    updated_at
) VALUES (
    'nayrouzdaikhi@gmail.com',
    '$2y$13$8.8c3qL3QKp0K3K3QKp0.uqL3QKp0K3K3QKp0K3K3QKp0K3K3QKp0K',
    'ROLE_USER',
    'Nayrouz',
    'Daikhi',
    'active',
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE updated_at = NOW();
