<?php

namespace App\Service;

use App\Entity\Commande;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel;
use Psr\Log\LoggerInterface;

class CommandeQrCodeService
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Generate a QR code as PNG Data URL for a Commande.
     */
    public function generateQrCodeDataUrl(Commande $commande): string
    {
        try {
            $this->logger->info('Starting QR code generation for order #' . $commande->getId());
            
            // Prepare commande data
            $qrData = "PHARMAX Order #" . $commande->getId() . "\n";
            $qrData .= "Total: " . $commande->getTotales() . " DT\n";
            $qrData .= "Date: " . ($commande->getCreatedAt()?->format('d/m/Y') ?? 'N/A') . "\n";
            
            // Count items
            $itemCount = 0;
            if ($commande->getLignes() && $commande->getLignes()->count() > 0) {
                $itemCount = $commande->getLignes()->count();
            } elseif ($commande->getProduits()) {
                $itemCount = count($commande->getProduits());
            }
            $qrData .= "Items: " . $itemCount . "\n";
            $qrData .= "Status: " . strtoupper($commande->getStatut());

            $this->logger->info('QR data prepared: ' . strlen($qrData) . ' bytes');

            // Generate QR code
            $qrCode = QrCode::create($qrData)
                ->setErrorCorrectionLevel(ErrorCorrectionLevel::High)
                ->setSize(250)
                ->setMargin(5);

            $this->logger->info('QR code object created');

            // Write to PNG
            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            $pngData = $result->getString();

            $this->logger->info('PNG data generated, size: ' . strlen($pngData) . ' bytes');

            if (empty($pngData)) {
                $this->logger->warning('QR code generation returned empty string');
                return '';
            }

            $dataUrl = 'data:image/png;base64,' . base64_encode($pngData);
            $this->logger->info('QR code data URL generated successfully, length: ' . strlen($dataUrl) . ' chars');
            
            return $dataUrl;
        } catch (\Exception $e) {
            $this->logger->error('QR code generation failed: ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
            return '';
        }
    }
}
