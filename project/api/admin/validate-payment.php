<?php
/**
 * Endpoint pour valider ou rejeter un paiement (admin)
 * POST /admin/validate-payment.php
 */

require_once '../config/database.php';
require_once '../config/cors.php';
require_once '../models/Payment.php';
require_once '../utils/auth.php';

// Vérification de la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array("message" => "Méthode non autorisée"));
    exit();
}

// Récupération des données JSON
$data = json_decode(file_get_contents("php://input"));

// Validation des données requises
if (empty($data->payment_id) || empty($data->action)) {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "ID du paiement et action requis"
    ));
    exit();
}

// Validation de l'action
if (!in_array($data->action, ['approve', 'reject'])) {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Action invalide. Utilisez 'approve' ou 'reject'"
    ));
    exit();
}

try {
    // Vérification de l'authentification admin
    if (!verifyAdminAuth()) {
        http_response_code(401);
        echo json_encode(array(
            "success" => false,
            "message" => "Accès non autorisé"
        ));
        exit();
    }

    // Connexion à la base de données
    $database = new Database();
    $db = $database->getConnection();

    // Validation du paiement
    $payment = new Payment($db);
    $result = $payment->validatePayment($data->payment_id, $data->action);

    if ($result) {
        $action_text = ($data->action === 'approve') ? 'validé' : 'rejeté';
        http_response_code(200);
        echo json_encode(array(
            "success" => true,
            "message" => "Paiement " . $action_text . " avec succès"
        ));
    } else {
        http_response_code(500);
        echo json_encode(array(
            "success" => false,
            "message" => "Erreur lors de la validation du paiement"
        ));
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Erreur serveur: " . $e->getMessage()
    ));
}
?>