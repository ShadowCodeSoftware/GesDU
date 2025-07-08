<?php
/**
 * Endpoint d'authentification pour les administrateurs
 * POST /auth/admin-login.php
 */

require_once '../config/database.php';
require_once '../config/cors.php';

// Vérification de la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array("message" => "Méthode non autorisée"));
    exit();
}

// Récupération des données JSON
$data = json_decode(file_get_contents("php://input"));

// Validation des données requises
if (empty($data->username) || empty($data->password)) {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Nom d'utilisateur et mot de passe requis"
    ));
    exit();
}

try {
    // Vérification des identifiants administrateur
    // En production, ces données devraient être en base de données avec hash
    $admin_username = "admin";
    $admin_password = "admin";

    if ($data->username === $admin_username && $data->password === $admin_password) {
        // Génération d'un token admin
        $token = base64_encode('admin:' . time());

        http_response_code(200);
        echo json_encode(array(
            "success" => true,
            "message" => "Connexion administrateur réussie",
            "token" => $token,
            "user_type" => "admin"
        ));
    } else {
        http_response_code(401);
        echo json_encode(array(
            "success" => false,
            "message" => "Identifiants administrateur incorrects"
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