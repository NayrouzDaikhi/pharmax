-- Nettoyer les doublons d'email en gardant le premier
-- Ce script supprimera tous les utilisateurs dupliqués sauf le premier pour chaque email

-- Étape 1: Identifier les IDs à supprimer (garder le plus ancien)
DELETE FROM user 
WHERE id NOT IN (
    SELECT MIN(id) FROM user GROUP BY email
) AND email IN (
    SELECT email FROM user GROUP BY email HAVING COUNT(*) > 1
);

-- Étape 2: Vérifier les résultats
SELECT email, COUNT(*) as count FROM user GROUP BY email HAVING count > 1;
