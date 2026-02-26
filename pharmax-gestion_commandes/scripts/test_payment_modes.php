<?php

function request($method, $url, &$cookieFile, $postFields = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    }
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($postFields !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        }
    }
    $resp = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return [$info, $resp];
}

$base = 'http://127.0.0.1:8000';

// Helper: run one flow with specified payment mode and output prefix
function runFlow($mode, $prefix, $base) {
    $cookie = __DIR__ . '/cookies_'.$prefix.'.txt';
    if (file_exists($cookie)) unlink($cookie);

    // Start session and add two products
    request('GET', $base . '/produits', $cookie);
    request('GET', $base . '/panier/ajouter/1', $cookie);
    request('GET', $base . '/panier/ajouter/2', $cookie);

    // View panier
    list($info, $body) = request('GET', $base . '/panier', $cookie);
    file_put_contents(__DIR__.'/'.$prefix.'_panier.html', $body);

    // Post commander with payment mode
    $post = http_build_query(['mode_paiement' => $mode]);
    list($info2, $body2) = request('POST', $base . '/panier/commander', $cookie, $post);
    file_put_contents(__DIR__.'/'.$prefix.'_commande_response.html', $body2);

    // If redirected to a paiement page, simulate payment confirmation (POST)
    if (isset($info2['url']) && preg_match('#/panier/paiement/(\d+)$#', $info2['url'], $m)) {
        $paiementUrl = $info2['url'];
        // POST to the payment URL to confirm
        list($info3, $body3) = request('POST', $paiementUrl, $cookie, []);
        file_put_contents(__DIR__.'/'.$prefix.'_paiement_response.html', $body3);
    }

    echo "Flow $prefix ($mode) done. Outputs: {$prefix}_panier.html, {$prefix}_commande_response.html" . (isset($body3) ? ", {$prefix}_paiement_response.html" : "") . "\n";
}

runFlow('a_livraison', 'livraison', $base);
runFlow('en_ligne', 'enligne', $base);

echo "All flows completed.\n";
