CREATE TABLE IF NOT EXISTS reset_password_request (
    id INT AUTO_INCREMENT NOT NULL,
    selector VARCHAR(20) NOT NULL,
    hashed_token VARCHAR(100) NOT NULL,
    requested_at DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    user_id INT NOT NULL,
    PRIMARY KEY(id),
    INDEX IDX_7CE748AA76ED395 (user_id),
    CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;