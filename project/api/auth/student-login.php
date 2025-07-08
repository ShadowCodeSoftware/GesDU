<?php
/**
 * Endpoint d'authentification pour les étudiants
 * POST /auth/student-login.php
 */

require_once '../config/database.php';
require_once '../config/cors.php';
require_once '../models/Student.php';

// Vérification de la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array("message" => "Méthode non autorisée"));
    exit();
}

// Récupération des données JSON
$data = json_decode(file_get_contents("php://input"));

// Validation des données requises
if (empty($data->matricule) || empty($data->password)) {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Matricule et mot de passe requis"
    ));
    exit();
}

try {
    // Connexion à la base de données
    $database = new Database();
    $db = $database->getConnection();

    // Création de l'objet Student
    $student = new Student($db);

    // Tentative de connexion
    $student_data = $student->login($data->matricule, $data->password);

    if ($student_data) {
        // Génération d'un token simple (en production, utiliser JWT)
        $token = base64_encode($student_data['id'] . ':' . time());

        http_response_code(200);
        echo json_encode(array(
            "success" => true,
            "message" => "Connexion réussie",
            "student" => $student_data,
            "token" => $token
        ));
    } else {
        http_response_code(401);
        echo json_encode(array(
            "success" => false,
            "message" => "Matricule ou mot de passe incorrect"
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