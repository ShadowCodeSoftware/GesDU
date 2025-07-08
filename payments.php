<?php
/**
 * API pour la gestion des paiements
 * 
 * Ce fichier gère toutes les opérations liées aux paiements :
 * - Enregistrement d'un nouveau paiement
 * - Consultation des paiements d'un étudiant
 * - Suivi du statut des paiements
 */

require_once 'config.php';

// Créer une instance de la classe Database
$database = new Database();
$db = $database->getConnection();

// Vérifier la connexion à la base de données
if ($db === null) {
    sendJsonResponse([
        'success' => false,
        'message' => 'Erreur de connexion à la base de données'
    ], 500);
}

// Récupérer la méthode HTTP
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGetRequest($db);
            break;
            
        case 'POST':
            handlePostRequest($db);
            break;
            
        default:
            sendJsonResponse([
                'success' => false,
                'message' => 'Méthode HTTP non autorisée'
            ], 405);
            break;
    }
} catch (Exception $e) {
    logError("Erreur dans payments.php: " . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'message' => 'Erreur interne du serveur'
    ], 500);
}

/**
 * Gère les requêtes GET pour récupérer les paiements
 * 
 * @param PDO $db Connexion à la base de données
 */
function handleGetRequest($db) {
    // Vérifier si un matricule est fourni pour la recherche
    if (isset($_GET['matricule'])) {
        $matricule = cleanInput($_GET['matricule']);
        
        if (!validateMatricule($matricule)) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Matricule invalide'
            ], 400);
        }
        
        // Récupérer les informations de l'étudiant et ses paiements
        $studentPaymentInfo = getStudentPaymentInfo($db, $matricule);
        
        if ($studentPaymentInfo) {
            sendJsonResponse([
                'success' => true,
                'data' => $studentPaymentInfo,
                'message' => 'Informations de paiement récupérées avec succès'
            ]);
        } else {
            sendJsonResponse([
                'success' => false,
                'message' => 'Étudiant non trouvé'
            ], 404);
        }
    } else {
        // Récupérer tous les paiements avec filtres optionnels
        $filters = [];
        $params = [];
        
        if (isset($_GET['statut'])) {
            $filters[] = "p.statut = :statut";
            $params['statut'] = cleanInput($_GET['statut']);
        }
        
        if (isset($_GET['tranche'])) {
            $filters[] = "p.tranche = :tranche";
            $params['tranche'] = intval($_GET['tranche']);
        }
        
        if (isset($_GET['annee_academique'])) {
            $filters[] = "p.annee_academique = :annee_academique";
            $params['annee_academique'] = cleanInput($_GET['annee_academique']);
        }
        
        $whereClause = !empty($filters) ? 'WHERE ' . implode(' AND ', $filters) : '';
        
        $sql = "SELECT p.*, s.nom, s.prenom 
                FROM payments p 
                LEFT JOIN students s ON p.student_id = s.id 
                $whereClause 
                ORDER BY p.date_paiement DESC";
                
        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();
        
        $payments = $stmt->fetchAll();
        
        sendJsonResponse([
            'success' => true,
            'data' => $payments,
            'message' => 'Liste des paiements récupérée avec succès'
        ]);
    }
}

/**
 * Gère les requêtes POST pour créer un nouveau paiement
 * 
 * @param PDO $db Connexion à la base de données
 */
function handlePostRequest($db) {
    // Lire les données JSON depuis le corps de la requête
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Données JSON invalides'
        ], 400);
    }
    
    // Valider les champs obligatoires
    $requiredFields = ['matricule', 'montant', 'filiere', 'faculte', 'tranche'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || ($field !== 'montant' && empty(trim($input[$field])))) {
            sendJsonResponse([
                'success' => false,
                'message' => "Le champ '$field' est obligatoire"
            ], 400);
        }
    }
    
    // Nettoyer et valider les données
    $matricule = cleanInput($input['matricule']);
    $montant = floatval($input['montant']);
    $filiere = cleanInput($input['filiere']);
    $faculte = cleanInput($input['faculte']);
    $universite = isset($input['universite']) ? cleanInput($input['universite']) : '';
    $annee_academique = isset($input['annee_academique']) ? cleanInput($input['annee_academique']) : '2024-2025';
    $tranche = intval($input['tranche']);
    
    // Validations
    if (!validateMatricule($matricule)) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Matricule invalide'
        ], 400);
    }
    
    if (!validatePaymentAmount($montant)) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Le montant doit être de ' . PAYMENT_AMOUNT . ' FCFA'
        ], 400);
    }
    
    if (!validateTranche($tranche)) {
        sendJsonResponse([
            'success' => false,
            'message' => 'La tranche doit être 1 ou 2'
        ], 400);
    }
    
    try {
        // Vérifier si l'étudiant existe
        $studentSql = "SELECT id FROM students WHERE matricule = :matricule";
        $studentStmt = $db->prepare($studentSql);
        $studentStmt->bindParam(':matricule', $matricule);
        $studentStmt->execute();
        
        $student = $studentStmt->fetch();
        
        if (!$student) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Étudiant non trouvé avec ce matricule'
            ], 404);
        }
        
        $studentId = $student['id'];
        
        // Vérifier si le paiement pour cette tranche existe déjà
        $checkSql = "SELECT id FROM payments WHERE matricule = :matricule AND tranche = :tranche AND annee_academique = :annee_academique";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->bindParam(':matricule', $matricule);
        $checkStmt->bindParam(':tranche', $tranche);
        $checkStmt->bindParam(':annee_academique', $annee_academique);
        $checkStmt->execute();
        
        if ($checkStmt->fetch()) {
            sendJsonResponse([
                'success' => false,
                'message' => "Le paiement pour la tranche $tranche de l'année $annee_academique existe déjà"
            ], 409);
        }
        
        // Insérer le nouveau paiement
        $sql = "INSERT INTO payments (student_id, matricule, montant, filiere, faculte, universite, annee_academique, tranche, statut) 
                VALUES (:student_id, :matricule, :montant, :filiere, :faculte, :universite, :annee_academique, :tranche, 'en_attente')";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':student_id', $studentId);
        $stmt->bindParam(':matricule', $matricule);
        $stmt->bindParam(':montant', $montant);
        $stmt->bindParam(':filiere', $filiere);
        $stmt->bindParam(':faculte', $faculte);
        $stmt->bindParam(':universite', $universite);
        $stmt->bindParam(':annee_academique', $annee_academique);
        $stmt->bindParam(':tranche', $tranche);
        
        if ($stmt->execute()) {
            $paymentId = $db->lastInsertId();
            
            logError("Nouveau paiement enregistré: Matricule $matricule, Tranche $tranche, Montant $montant FCFA", 'INFO');
            
            sendJsonResponse([
                'success' => true,
                'data' => [
                    'id' => $paymentId,
                    'matricule' => $matricule,
                    'montant' => $montant,
                    'tranche' => $tranche,
                    'statut' => 'en_attente'
                ],
                'message' => 'Paiement enregistré avec succès. En attente de validation.'
            ], 201);
        } else {
            sendJsonResponse([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement du paiement'
            ], 500);
        }
        
    } catch (PDOException $e) {
        logError("Erreur PDO lors du paiement: " . $e->getMessage());
        
        // Vérifier si c'est une erreur de duplication
        if ($e->getCode() == 23000) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Ce paiement existe déjà'
            ], 409);
        } else {
            sendJsonResponse([
                'success' => false,
                'message' => 'Erreur de base de données lors du paiement'
            ], 500);
        }
    }
}

/**
 * Récupère les informations complètes de paiement d'un étudiant
 * 
 * @param PDO $db Connexion à la base de données
 * @param string $matricule Matricule de l'étudiant
 * @return array|null Informations de paiement ou null si non trouvé
 */
function getStudentPaymentInfo($db, $matricule) {
    try {
        // Récupérer les informations de l'étudiant
        $studentSql = "SELECT * FROM students WHERE matricule = :matricule";
        $studentStmt = $db->prepare($studentSql);
        $studentStmt->bindParam(':matricule', $matricule);
        $studentStmt->execute();
        
        $student = $studentStmt->fetch();
        
        if (!$student) {
            return null;
        }
        
        // Récupérer tous les paiements de cet étudiant
        $paymentsSql = "SELECT * FROM payments WHERE matricule = :matricule ORDER BY tranche ASC, date_paiement DESC";
        $paymentsStmt = $db->prepare($paymentsSql);
        $paymentsStmt->bindParam(':matricule', $matricule);
        $paymentsStmt->execute();
        
        $payments = $paymentsStmt->fetchAll();
        
        // Calculer les totaux
        $totalPaye = 0;
        foreach ($payments as $payment) {
            if ($payment['statut'] === 'valide') {
                $totalPaye += $payment['montant'];
            }
        }
        
        $totalRestant = TOTAL_AMOUNT - $totalPaye;
        
        return [
            'student' => $student,
            'payments' => $payments,
            'total_paye' => $totalPaye,
            'total_restant' => max(0, $totalRestant),
            'statut_global' => $totalPaye >= TOTAL_AMOUNT ? 'complet' : ($totalPaye > 0 ? 'partiel' : 'non_paye')
        ];
        
    } catch (PDOException $e) {
        logError("Erreur lors de la récupération des informations de paiement: " . $e->getMessage());
        return null;
    }
}
?>