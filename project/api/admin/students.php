<?php
/**
 * Endpoint pour récupérer tous les étudiants (admin)
 * GET /admin/students.php
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

    // Récupération de tous les étudiants avec leur statut de paiement
    $student = new Student($db);
    $students = $student->getAllWithPaymentStatus();

    http_response_code(200);
    echo json_encode(array(
        "success" => true,
        "students" => $students
    ));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Erreur serveur: " . $e->getMessage()
    ));
}
?>