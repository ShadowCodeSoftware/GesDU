<?php
/**
 * Endpoint pour effectuer un paiement
 * POST /payment/make-payment.php
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
if (empty($data->montant) || empty($data->tranche) || empty($data->matricule)) {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Données de paiement incomplètes"
    ));
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

    // Validation du montant (doit être 25000)
    if ($data->montant != 25000) {
        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Montant invalide. Chaque tranche coûte 25,000 FCFA"
        ));
        exit();
    }

    // Validation de la tranche (1 ou 2)
    if (!in_array($data->tranche, [1, 2])) {
        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Numéro de tranche invalide"
        ));
        exit();
    }

    // Connexion à la base de données
    $database = new Database();
    $db = $database->getConnection();

    // Vérification si le paiement existe déjà
    $payment = new Payment($db);
    if ($payment->checkExistingPayment($student_id, $data->tranche)) {
        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Paiement déjà effectué pour cette tranche"
        ));
        exit();
    }

    // Création du paiement
    $payment->etudiant_id = $student_id;
    $payment->montant = $data->montant;
    $payment->tranche = $data->tranche;
    $payment->filiere = $data->filiere ?? '';
    $payment->faculte = $data->faculte ?? '';
    $payment->universite = $data->universite ?? '';
    $payment->annee_academique = $data->annee_academique ?? '';

    if ($payment->create()) {
        http_response_code(201);
        echo json_encode(array(
            "success" => true,
            "message" => "Paiement effectué avec succès. En attente de validation."
        ));
    } else {
        http_response_code(500);
        echo json_encode(array(
            "success" => false,
            "message" => "Erreur lors de l'enregistrement du paiement"
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