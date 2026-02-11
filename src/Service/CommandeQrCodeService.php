<?php

namespace App\Service;

use App\Entity\Commande;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Encoding\Encoding;

class CommandeQrCodeService
{
    /**
     * Generate a QR code as SVG Data URL for a Commande.
     * SVG doesn't require GD extension.
     */
    public function generateQrCodeDataUrl(Commande $commande): string
    {
        // Prepare commande data similar to invoice/facture
        $qrData = "=== PHARMAX COMMANDE ===\n";
        $qrData .= sprintf("ID: #%d\n", $commande->getId());
        $qrData .= sprintf("Date: %s\n", $commande->getCreatedAt()?->format('d/m/Y H:i') ?? 'N/A');
        $qrData .= sprintf("Client: %s\n", $commande->getUtilisateur()?->getEmail() ?? 'Anonyme');
        $qrData .= "\n--- PRODUITS ---\n";
        
        // Add product details from lignes (invoice lines)
        if ($commande->getLignes() && $commande->getLignes()->count() > 0) {
            foreach ($commande->getLignes() as $ligne) {
                $qrData .= sprintf(
                    "%s | %.2f TND x%d = %.2f TND\n",
                    $ligne->getNom(),
                    $ligne->getPrix(),
                    $ligne->getQuantite(),
                    $ligne->getSousTotal()
                );
            }
        } elseif ($commande->getProduits()) {
            // Fallback for legacy produits format
            foreach ($commande->getProduits() as $p) {
                $nom = is_array($p) ? ($p['nom'] ?? 'Unknown') : (is_object($p) ? ($p->nom ?? 'Unknown') : 'Unknown');
                $prix = is_array($p) ? ($p['prix'] ?? 0) : (is_object($p) ? ($p->prix ?? 0) : 0);
                $quantite = is_array($p) ? ($p['quantite'] ?? 1) : (is_object($p) ? ($p->quantite ?? 1) : 1);
                $sousTotal = $prix * $quantite;
                $qrData .= sprintf("%s | %.2f TND x%d = %.2f TND\n", $nom, $prix, $quantite, $sousTotal);
            }
        }
        
        $qrData .= "\n--- TOTAL ---\n";
        $qrData .= sprintf("%.2f TND\n", $commande->getTotales());
        $qrData .= sprintf("Statut: %s\n", strtoupper($commande->getStatut()));

        // Generate QR code
        $qrCode = new QrCode($qrData);

        // Write to SVG
        $writer = new SvgWriter();
        $result = $writer->write($qrCode);

        // Return as data URL
        $svgString = $result->getString();
        return 'data:image/svg+xml;base64,' . base64_encode($svgString);
    }
}
