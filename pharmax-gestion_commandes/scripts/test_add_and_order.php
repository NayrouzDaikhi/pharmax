<?php

function request($method, $url, &$cookieFile, $postFields = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_VERBOSE, false);
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
$cookie = __DIR__ . '/cookies_test.txt';
if (file_exists($cookie)) unlink($cookie);

// Visit products page to get session cookie
request('GET', $base . '/produits', $cookie);
// Add product 1
request('GET', $base . '/panier/ajouter/1', $cookie);
// Add product 2
request('GET', $base . '/panier/ajouter/2', $cookie);
// View panier
list($info, $body) = request('GET', $base . '/panier', $cookie);
file_put_contents(__DIR__.'/panier_page.html', $body);

// Post commander
list($info2, $body2) = request('POST', $base . '/panier/commander', $cookie, []);
file_put_contents(__DIR__.'/commande_response.html', $body2);

echo "Done. See scripts/panier_page.html and scripts/commande_response.html\n";
