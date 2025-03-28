<?php
// Ce script traite la soumission du formulaire et appelle l'API Tron correspondante.

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Récupérer et nettoyer les données du formulaire
    $apiKey    = trim($_POST['api_key']);        // API Key saisie par l'utilisateur
    $recipient = trim($_POST['recipient']);        // Adresse du destinataire
    $amount    = floatval($_POST['amount']);       // Montant en USDT
    $network   = trim($_POST['network']);          // Réseau sélectionné ("shasta" ou "monad")

    // Adresse du contrat USDT sur Testnet (les deux réseaux utilisent ici le même contrat pour l'exemple)
    $usdtContract = 'TN3W4H6rKZUog2LyCqTzyM6s5oE4W3s5yA';

    // Conversion du montant en "Sun" (1 USDT = 1 000 000 Sun)
    $amountInSun = $amount * 1000000;

    // Choix de l'URL de l'API Tron selon le réseau sélectionné
    if ($network === 'monad') {
        $tronApiUrl = 'https://api.monad.trongrid.io/wallet/triggerconstantcontract';
    } else {
        $tronApiUrl = 'https://api.shasta.trongrid.io/wallet/triggerconstantcontract';
    }

    /*
      Pour appeler la fonction "transfer(address,uint256)" du contrat,
      il est nécessaire d'encoder les paramètres selon l'ABI de Tron.
      Ici, nous créons un paramètre fictif en encodant en base64 un JSON.
      En production, il faudra utiliser une librairie d'encodage ABI adaptée.
    */
    $parameter = base64_encode(json_encode([
        'recipient' => $recipient,
        'amount'    => $amountInSun
    ]));

    // Préparer les données à envoyer à l'API
    $data = [
        'contract_address'  => $usdtContract,
        'function_selector' => 'transfer(address,uint256)',
        'parameter'         => $parameter,
        'fee_limit'         => 100000000,
    ];

    // Conversion du tableau en JSON
    $payload = json_encode($data);

    // Préparer les entêtes HTTP, en incluant l'API Key saisie par l'utilisateur
    $headers = [
        'Content-Type: application/json',
        'TRON-PRO-API-KEY: ' . $apiKey,
    ];

    // Initialiser cURL pour effectuer l'appel à l'API
    $ch = curl_init($tronApiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Exécuter la requête
    $response = curl_exec($ch);

    // Vérifier les erreurs de cURL
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        echo "<h2>Erreur cURL :</h2>";
        echo "<pre>" . htmlspecialchars($error_msg) . "</pre>";
        exit;
    } else {
        curl_close($ch);
        // Afficher la réponse de l'API Tron
        echo "<h2>Réponse de l'API Tron :</h2>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }
} else {
    echo "Aucune donnée reçue.";
}
?>
