<?php
/**
 * Endpoint pour récupérer les statistiques du dashboard admin
 * GET /admin/dashboard-stats.php
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

    // Récupération des statistiques
    $payment = new Payment($db);
    $stats = $payment->getPaymentStats();

    http_response_code(200);
    echo json_encode(array(
        "success" => true,
        "stats" => array(
            "totalStudents" => (int)$stats['total_students'],
            "totalPayments" => (int)$stats['total_payments'],
            "pendingPayments" => (int)$stats['pending_payments'],
            "totalAmount" => (int)$stats['total_amount'],
            "paidAmount" => (int)$stats['paid_amount']
        )
    ));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Erreur serveur: " . $e->getMessage()
    ));
}
?>