<?php
/**
 * Endpoint pour récupérer le profil étudiant
 * GET /student/profile.php
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

    // Récupération du profil étudiant
    $student = new Student($db);
    $student_data = $student->getById($student_id);

    if ($student_data) {
        http_response_code(200);
        echo json_encode(array(
            "success" => true,
            "student" => $student_data
        ));
    } else {
        http_response_code(404);
        echo json_encode(array(
            "success" => false,
            "message" => "Étudiant non trouvé"
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