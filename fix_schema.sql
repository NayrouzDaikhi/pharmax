-- Add missing user_id column to reponse table
ALTER TABLE reponse ADD COLUMN user_id INT DEFAULT NULL;
ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC7A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL;
CREATE INDEX IDX_5FB6DEC7A76ED395 ON reponse (user_id);

-- Verify reclamation table has user_id (if not present)
-- This should already exist based on schema definition
