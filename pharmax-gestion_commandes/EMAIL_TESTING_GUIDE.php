<?php
/**
 * TESTING EMAILS LOCALLY
 * 
 * Avec MAILER_DSN=null://null dans .env, les emails sont capturés localement
 * 
 * ÉTAPES DE TEST :
 * 
 * 1. Allez sur http://localhost:8000/
 * 2. Cliquez sur un produit
 * 3. Cliquez "Ajouter au panier"
 * 4. Allez au panier (cliquez l'icône panier en haut)
 * 5. Cliquez "Confirmer la commande"
 * 6. Vous serez redirigé vers la page de confirmation
 * 7. L'email a été capturé et envoyé localement
 * 
 * POUR VOIR LES EMAILS :
 * 
 * Option A - LOGS (Défaut avec null://null)
 * - Ouvrez : var/log/dev.log
 * - Les emails y sont enregistrés
 * - Cherchez : "Sending a message using the Null transport"
 * 
 * Option B - DEBUG BAR
 * - En bas à droite de chaque page (en dev)
 * - Consultez l'onglet "Emails"
 * 
 * Option C - MAILTRAP (Gratuit)
 * - Inscrivez-vous : https://mailtrap.io
 * - Copiez l'SMTP DSN
 * - Remplacez dans .env : MAILER_DSN=ta_clé_mailtrap
 * - Les emails s'accumuleront dans votre inbox Mailtrap
 * 
 * EXEMPLE DE SORTIE CONSOLE :
 * 
 * POST /panier/commander
 * → Création Commande #25
 * → Envoi email "Confirmation de commande #25"
 * → À: orders@pharmax.example, user@example.com
 * → Template: emails/commande_confirmation.html.twig
 * → Status: OK (capturé localement)
 * → Redirection vers page de confirmation
 */
?>
