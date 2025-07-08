<?php
/**
 * Endpoint pour récupérer le statut de paiement d'un étudiant
 * GET /student/payment-status.php
 */

require_once '../config/database.php';
require_once '../config/cors.php';
require_once '../models/Student.php';
require_once '../utils/auth.php';

// Vérification de la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(array("message" => "Méthode non autorisée"));
    exit();
}

try {
    // Vérification de l'authentification
    $student_id = verifyStudentAuth();
    
    if (!$student_id) {
        http_response_code(401);
        echo json_encode(array(
            "success" => false,
            "message" => "Token d'authentification invalide"
        ));
        exit();
    }

    // Connexion à la base de données
    $database = new Database();
    $db = $database->getConnection();

    // Récupération du statut de paiement
    $student = new Student($db);
    $payment_status = $student->getPaymentStatus($student_id);

    http_response_code(200);
    echo json_encode(array(
        "success" => true,
        "status" => $payment_status
    ));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Erreur serveur: " . $e->getMessage()
    ));
}
?>