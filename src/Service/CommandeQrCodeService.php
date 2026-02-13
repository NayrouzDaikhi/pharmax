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
            
            // Add product names and quantities
            $products = [];
            if ($commande->getLignes() && $commande->getLignes()->count() > 0) {
                foreach ($commande->getLignes() as $ligne) {
                    $produitNom = $ligne->getNom();
                    $quantite = $ligne->getQuantite();
                    $products[] = $quantite . "x " . substr($produitNom, 0, 30);
                }
            }
            
            $itemCount = count($products);
            $qrData .= "Items: " . $itemCount . "\n";
            
            // Add product details
            if (!empty($products)) {
                $qrData .= "---\n";
                foreach ($products as $product) {
                    $qrData .= $product . "\n";
                }
            }
            
            $qrData .= "Status: " . strtoupper($commande->getStatut());

            $this->logger->info('QR data prepared: ' . strlen($qrData) . ' bytes');

            // Generate QR code using v5.1 API
            try {
                $qrCode = QrCode::create($qrData)
                    ->setErrorCorrectionLevel(ErrorCorrectionLevel::High)
                    ->setSize(250)
                    ->setMargin(5);

                $this->logger->info('QR code object created successfully');

                // Write to PNG
                $writer = new PngWriter();
                $result = $writer->write($qrCode);
                
                // Get PNG data from the result object
                $pngData = null;
                if (method_exists($result, 'getString')) {
                    $pngData = $result->getString();
                } elseif (method_exists($result, 'getStream')) {
                    $pngData = stream_get_contents($result->getStream());
                } else {
                    // Try to convert to string directly
                    $pngData = (string)$result;
                }

                $this->logger->info('PNG data generated, size: ' . strlen($pngData ?? '') . ' bytes');

                if (empty($pngData)) {
                    $this->logger->warning('QR code generation: Empty PNG data returned');
                    // Return fallback - generate QR code with simpler method
                    return $this->generateFallbackQrCode($qrData);
                }

                $dataUrl = 'data:image/png;base64,' . base64_encode($pngData);
                $this->logger->info('QR code data URL generated successfully, length: ' . strlen($dataUrl) . ' chars');
                
                return $dataUrl;
            } catch (\Exception $e) {
                $this->logger->error('QR code library error: ' . $e->getMessage());
                $this->logger->error('Stack trace: ' . $e->getTraceAsString());
                // Return fallback
                return $this->generateFallbackQrCode($qrData);
            }
        } catch (\Exception $e) {
            $this->logger->error('QR code generation failed: ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
            return '';
        }
    }

    /**
     * Generate a fallback QR code using alternative method
     */
    private function generateFallbackQrCode(string $data): string
    {
        try {
            $this->logger->info('Using fallback QR code generation');
            
            // Use a free QR code API as fallback
            $encodedData = urlencode($data);
            $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . $encodedData;
            
            $this->logger->info('Fallback QR code URL: ' . $qrUrl);
            
            // Safely fetch the QR code from external API
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                ]
            ]);
            
            $pngData = @file_get_contents($qrUrl, false, $context);
            
            if (!empty($pngData)) {
                $dataUrl = 'data:image/png;base64,' . base64_encode($pngData);
                $this->logger->info('Fallback QR code generated successfully');
                return $dataUrl;
            }
            
            $this->logger->warning('Fallback QR code: Could not fetch from API');
            return '';
        } catch (\Exception $e) {
            $this->logger->error('Fallback QR code generation failed: ' . $e->getMessage());
            return '';
        }
    }
}

