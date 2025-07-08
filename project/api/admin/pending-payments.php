<?php
/**
 * Endpoint pour récupérer les paiements en attente (admin)
 * GET /admin/pending-payments.php
 */

require_once '../config/database.php';
require_once '../config/cors.php';
require_once '../models/Payment.php';
require_once '../utils/auth.php';

// Vérification de la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(array("message" => "Méthode non autorisée"));
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

    // Récupération des paiements en attente
    $payment = new Payment($db);
    $payments = $payment->getPendingPayments();

    http_response_code(200);
    echo json_encode(array(
        "success" => true,
        "payments" => $payments
    ));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Erreur serveur: " . $e->getMessage()
    ));
}
?>