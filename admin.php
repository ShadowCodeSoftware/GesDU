<?php
/**
 * API pour l'administration bancaire
 * 
 * Ce fichier gère les fonctionnalités réservées aux administrateurs :
 * - Authentification des administrateurs
 * - Validation des paiements
 * - Gestion des paiements en attente
 * - Statistiques administratives
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
    logError("Erreur dans admin.php: " . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'message' => 'Erreur interne du serveur'
    ], 500);
}

/**
 * Gère les requêtes GET pour récupérer les données d'administration
 * 
 * @param PDO $db Connexion à la base de données
 */
function handleGetRequest($db) {
    $action = isset($_GET['action']) ? cleanInput($_GET['action']) : '';
    
    switch ($action) {
        case 'pending_payments':
            getPendingPayments($db);
            break;
            
        case 'payment_history':
            getPaymentHistory($db);
            break;
            
        case 'admin_stats':
            getAdminStats($db);
            break;
            
        default:
            sendJsonResponse([
                'success' => false,
                'message' => 'Action non spécifiée ou invalide'
            ], 400);
            break;
    }
}

/**
 * Gère les requêtes POST pour les actions d'administration
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
    
    $action = isset($input['action']) ? cleanInput($input['action']) : '';
    
    switch ($action) {
        case 'login':
            handleAdminLogin($input);
            break;
            
        case 'validate_payment':
            validatePayment($db, $input);
            break;
            
        default:
            sendJsonResponse([
                'success' => false,
                'message' => 'Action non spécifiée ou invalide'
            ], 400);
            break;
    }
}

/**
 * Gère l'authentification des administrateurs
 * 
 * @param array $input Données de connexion
 */
function handleAdminLogin($input) {
    // Valider les champs obligatoires
    if (!isset($input['username']) || !isset($input['password'])) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Nom d\'utilisateur et mot de passe requis'
        ], 400);
    }
    
    $username = cleanInput($input['username']);
    $password = cleanInput($input['password']);
    
    // Vérification simple (dans un vrai projet, utiliser des mots de passe hachés)
    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        // Générer un token simple (dans un vrai projet, utiliser JWT)
        $token = base64_encode($username . ':' . time());
        
        logError("Connexion administrateur réussie: $username", 'INFO');
        
        sendJsonResponse([
            'success' => true,
            'data' => [
                'username' => $username,
                'token' => $token,
                'role' => 'admin'
            ],
            'message' => 'Connexion réussie'
        ]);
    } else {
        logError("Tentative de connexion échouée: $username", 'WARNING');
        
        sendJsonResponse([
            'success' => false,
            'message' => 'Identifiants incorrects'
        ], 401);
    }
}

/**
 * Récupère la liste des paiements en attente de validation
 * 
 * @param PDO $db Connexion à la base de données
 */
function getPendingPayments($db) {
    try {
        $sql = "SELECT p.*, s.nom, s.prenom, 
                       CONCAT(s.nom, ' ', s.prenom) as student_name
                FROM payments p 
                LEFT JOIN students s ON p.student_id = s.id 
                WHERE p.statut = 'en_attente' 
                ORDER BY p.date_paiement ASC";
                
        $stmt = $db->prepare($sql);
        $stmt->execute();
        
        $pendingPayments = $stmt->fetchAll();
        
        sendJsonResponse([
            'success' => true,
            'data' => $pendingPayments,
            'message' => 'Paiements en attente récupérés avec succès'
        ]);
        
    } catch (PDOException $e) {
        logError("Erreur lors de la récupération des paiements en attente: " . $e->getMessage());
        sendJsonResponse([
            'success' => false,
            'message' => 'Erreur lors de la récupération des paiements'
        ], 500);
    }
}

/**
 * Valide ou rejette un paiement
 * 
 * @param PDO $db Connexion à la base de données
 * @param array $input Données de validation
 */
function validatePayment($db, $input) {
    // Valider les champs obligatoires
    if (!isset($input['payment_id']) || !isset($input['status'])) {
        sendJsonResponse([
            'success' => false,
            'message' => 'ID du paiement et statut requis'
        ], 400);
    }
    
    $paymentId = intval($input['payment_id']);
    $status = cleanInput($input['status']);
    $adminValidateur = isset($input['admin']) ? cleanInput($input['admin']) : 'admi';
    $commentaire = isset($input['commentaire']) ? cleanInput($input['commentaire']) : '';
    
    // Valider le statut
    if (!in_array($status, ['valide', 'rejete'])) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Statut invalide. Doit être "valide" ou "rejete"'
        ], 400);
    }
    
    try {
        // Vérifier que le paiement existe et est en attente
        $checkSql = "SELECT p.*, s.nom, s.prenom FROM payments p 
                     LEFT JOIN students s ON p.student_id = s.id 
                     WHERE p.id = :payment_id AND p.statut = 'en_attente'";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->bindParam(':payment_id', $paymentId);
        $checkStmt->execute();
        
        $payment = $checkStmt->fetch();
        
        if (!$payment) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Paiement non trouvé ou déjà traité'
            ], 404);
        }
        
        // Mettre à jour le statut du paiement
        $updateSql = "UPDATE payments 
                      SET statut = :status, 
                          date_validation = CURRENT_TIMESTAMP, 
                          admin_validateur = :admin_validateur, 
                          commentaire = :commentaire 
                      WHERE id = :payment_id";
        
        $updateStmt = $db->prepare($updateSql);
        $updateStmt->bindParam(':status', $status);
        $updateStmt->bindParam(':admin_validateur', $adminValidateur);
        $updateStmt->bindParam(':commentaire', $commentaire);
        $updateStmt->bindParam(':payment_id', $paymentId);
        
        if ($updateStmt->execute()) {
            $actionText = $status === 'valide' ? 'validé' : 'rejeté';
            
            logError("Paiement $actionText: ID=$paymentId, Étudiant={$payment['nom']} {$payment['prenom']}, Montant={$payment['montant']} FCFA", 'INFO');
            
            sendJsonResponse([
                'success' => true,
                'data' => [
                    'payment_id' => $paymentId,
                    'status' => $status,
                    'student_name' => $payment['nom'] . ' ' . $payment['prenom'],
                    'amount' => $payment['montant']
                ],
                'message' => "Paiement $actionText avec succès"
            ]);
        } else {
            sendJsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du paiement'
            ], 500);
        }
        
    } catch (PDOException $e) {
        logError("Erreur lors de la validation du paiement: " . $e->getMessage());
        sendJsonResponse([
            'success' => false,
            'message' => 'Erreur de base de données'
        ], 500);
    }
}

/**
 * Récupère l'historique des paiements avec filtres
 * 
 * @param PDO $db Connexion à la base de données
 */
function getPaymentHistory($db) {
    try {
        $filters = [];
        $params = [];
        
        // Filtres optionnels
        if (isset($_GET['statut'])) {
            $filters[] = "p.statut = :statut";
            $params['statut'] = cleanInput($_GET['statut']);
        }
        
        if (isset($_GET['date_debut'])) {
            $filters[] = "DATE(p.date_paiement) >= :date_debut";
            $params['date_debut'] = cleanInput($_GET['date_debut']);
        }
        
        if (isset($_GET['date_fin'])) {
            $filters[] = "DATE(p.date_paiement) <= :date_fin";
            $params['date_fin'] = cleanInput($_GET['date_fin']);
        }
        
        if (isset($_GET['admin_validateur'])) {
            $filters[] = "p.admin_validateur = :admin_validateur";
            $params['admin_validateur'] = cleanInput($_GET['admin_validateur']);
        }
        
        $whereClause = !empty($filters) ? 'WHERE ' . implode(' AND ', $filters) : '';
        
        $sql = "SELECT p.*, s.nom, s.prenom, 
                       CONCAT(s.nom, ' ', s.prenom) as student_name
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
            'message' => 'Historique des paiements récupéré avec succès'
        ]);
        
    } catch (PDOException $e) {
        logError("Erreur lors de la récupération de l'historique: " . $e->getMessage());
        sendJsonResponse([
            'success' => false,
            'message' => 'Erreur lors de la récupération de l\'historique'
        ], 500);
    }
}

/**
 * Récupère les statistiques pour l'administration
 * 
 * @param PDO $db Connexion à la base de données
 */
function getAdminStats($db) {
    try {
        // Statistiques générales
        $statsSql = "SELECT 
                        (SELECT COUNT(*) FROM payments WHERE statut = 'en_attente') as pending_payments,
                        (SELECT COUNT(*) FROM payments WHERE statut = 'valide') as validated_payments,
                        (SELECT COUNT(*) FROM payments WHERE statut = 'rejete') as rejected_payments,
                        (SELECT COALESCE(SUM(montant), 0) FROM payments WHERE statut = 'valide') as total_amount,
                        (SELECT COUNT(*) FROM students) as total_students,
                        (SELECT COUNT(DISTINCT matricule) FROM payments WHERE statut = 'valide') as paying_students";
        
        $statsStmt = $db->prepare($statsSql);
        $statsStmt->execute();
        $stats = $statsStmt->fetch();
        
        // Statistiques par tranche
        $trancheSql = "SELECT tranche, COUNT(*) as count, SUM(montant) as total 
                       FROM payments 
                       WHERE statut = 'valide' 
                       GROUP BY tranche";
        $trancheStmt = $db->prepare($trancheSql);
        $trancheStmt->execute();
        $trancheStats = $trancheStmt->fetchAll();
        
        // Statistiques par filière
        $filiereSql = "SELECT filiere, COUNT(*) as count, SUM(montant) as total 
                       FROM payments 
                       WHERE statut = 'valide' 
                       GROUP BY filiere 
                       ORDER BY total DESC 
                       LIMIT 10";
        $filiereStmt = $db->prepare($filiereSql);
        $filiereStmt->execute();
        $filiereStats = $filiereStmt->fetchAll();
        
        sendJsonResponse([
            'success' => true,
            'data' => [
                'general' => $stats,
                'par_tranche' => $trancheStats,
                'par_filiere' => $filiereStats
            ],
            'message' => 'Statistiques administratives récupérées avec succès'
        ]);
        
    } catch (PDOException $e) {
        logError("Erreur lors de la récupération des statistiques: " . $e->getMessage());
        sendJsonResponse([
            'success' => false,
            'message' => 'Erreur lors de la récupération des statistiques'
        ], 500);
    }
}
?>