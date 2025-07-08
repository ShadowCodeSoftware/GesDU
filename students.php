<?php
/**
 * API pour la gestion des étudiants
 * 
 * Ce fichier gère toutes les opérations CRUD pour les étudiants :
 * - Inscription d'un nouvel étudiant
 * - Recherche d'un étudiant par matricule
 * - Liste de tous les étudiants
 * - Modification des informations d'un étudiant
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
            
        case 'PUT':
            handlePutRequest($db);
            break;
            
        default:
            sendJsonResponse([
                'success' => false,
                'message' => 'Méthode HTTP non autorisée'
            ], 405);
            break;
    }
} catch (Exception $e) {
    logError("Erreur dans students.php: " . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'message' => 'Erreur interne du serveur'
    ], 500);
}

/**
 * Gère les requêtes GET pour récupérer des étudiants
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
        
        // Rechercher un étudiant spécifique par matricule
        $sql = "SELECT * FROM students WHERE matricule = :matricule";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':matricule', $matricule);
        $stmt->execute();
        
        $student = $stmt->fetch();
        
        if ($student) {
            sendJsonResponse([
                'success' => true,
                'data' => $student,
                'message' => 'Étudiant trouvé avec succès'
            ]);
        } else {
            sendJsonResponse([
                'success' => false,
                'message' => 'Étudiant non trouvé'
            ], 404);
        }
    } else {
        // Récupérer tous les étudiants avec pagination optionnelle
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = isset($_GET['limit']) ? min(100, max(10, intval($_GET['limit']))) : 50;
        $offset = ($page - 1) * $limit;
        
        // Filtres optionnels
        $whereConditions = [];
        $params = [];
        
        if (isset($_GET['filiere']) && !empty($_GET['filiere'])) {
            $whereConditions[] = "filiere LIKE :filiere";
            $params['filiere'] = '%' . cleanInput($_GET['filiere']) . '%';
        }
        
        if (isset($_GET['niveau']) && !empty($_GET['niveau'])) {
            $whereConditions[] = "niveau = :niveau";
            $params['niveau'] = cleanInput($_GET['niveau']);
        }
        
        if (isset($_GET['annee_academique']) && !empty($_GET['annee_academique'])) {
            $whereConditions[] = "annee_academique = :annee_academique";
            $params['annee_academique'] = cleanInput($_GET['annee_academique']);
        }
        
        // Construire la requête SQL
        $whereSql = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        // Compter le total d'étudiants
        $countSql = "SELECT COUNT(*) as total FROM students $whereSql";
        $countStmt = $db->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue(':' . $key, $value);
        }
        $countStmt->execute();
        $totalStudents = $countStmt->fetch()['total'];
        
        // Récupérer les étudiants avec pagination
        $sql = "SELECT * FROM students $whereSql ORDER BY nom, prenom LIMIT :limit OFFSET :offset";
        $stmt = $db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $students = $stmt->fetchAll();
        
        sendJsonResponse([
            'success' => true,
            'data' => $students,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => intval($totalStudents),
                'total_pages' => ceil($totalStudents / $limit)
            ],
            'message' => 'Liste des étudiants récupérée avec succès'
        ]);
    }
}

/**
 * Gère les requêtes POST pour créer un nouvel étudiant
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
    $requiredFields = ['nom', 'prenom', 'matricule', 'filiere'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty(trim($input[$field]))) {
            sendJsonResponse([
                'success' => false,
                'message' => "Le champ '$field' est obligatoire"
            ], 400);
        }
    }
    
    // Nettoyer et valider les données
    $nom = cleanInput($input['nom']);
    $prenom = cleanInput($input['prenom']);
    $sexe = isset($input['sexe']) ? cleanInput($input['sexe']) : 'M';
    $filiere = cleanInput($input['filiere']);
    $departement = isset($input['departement']) ? cleanInput($input['departement']) : '';
    $matricule = cleanInput($input['matricule']);
    $niveau = isset($input['niveau']) ? cleanInput($input['niveau']) : '';
    $annee_academique = isset($input['annee_academique']) ? cleanInput($input['annee_academique']) : '2024-2025';
    $annee_naissance = isset($input['annee_naissance']) ? cleanInput($input['annee_naissance']) : null;
    $lieu = isset($input['lieu']) ? cleanInput($input['lieu']) : '';
    
    // Valider le matricule
    if (!validateMatricule($matricule)) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Le matricule doit contenir au moins 3 caractères alphanumériques'
        ], 400);
    }
    
    // Valider le sexe
    if (!in_array($sexe, ['M', 'F'])) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Le sexe doit être M ou F'
        ], 400);
    }
    
    try {
        // Vérifier si le matricule existe déjà
        $checkSql = "SELECT id FROM students WHERE matricule = :matricule";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->bindParam(':matricule', $matricule);
        $checkStmt->execute();
        
        if ($checkStmt->fetch()) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Un étudiant avec ce matricule existe déjà'
            ], 409);
        }
        
        // Insérer le nouvel étudiant
        $sql = "INSERT INTO students (nom, prenom, sexe, filiere, departement, matricule, niveau, annee_academique, annee_naissance, lieu) 
                VALUES (:nom, :prenom, :sexe, :filiere, :departement, :matricule, :niveau, :annee_academique, :annee_naissance, :lieu)";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':sexe', $sexe);
        $stmt->bindParam(':filiere', $filiere);
        $stmt->bindParam(':departement', $departement);
        $stmt->bindParam(':matricule', $matricule);
        $stmt->bindParam(':niveau', $niveau);
        $stmt->bindParam(':annee_academique', $annee_academique);
        $stmt->bindParam(':annee_naissance', $annee_naissance);
        $stmt->bindParam(':lieu', $lieu);
        
        if ($stmt->execute()) {
            $studentId = $db->lastInsertId();
            
            logError("Nouvel étudiant inscrit: $nom $prenom (Matricule: $matricule)", 'INFO');
            
            sendJsonResponse([
                'success' => true,
                'data' => [
                    'id' => $studentId,
                    'matricule' => $matricule,
                    'nom' => $nom,
                    'prenom' => $prenom
                ],
                'message' => 'Étudiant inscrit avec succès'
            ], 201);
        } else {
            sendJsonResponse([
                'success' => false,
                'message' => 'Erreur lors de l\'inscription de l\'étudiant'
            ], 500);
        }
        
    } catch (PDOException $e) {
        logError("Erreur PDO lors de l'inscription: " . $e->getMessage());
        
        // Vérifier si c'est une erreur de duplication
        if ($e->getCode() == 23000) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Un étudiant avec ce matricule existe déjà'
            ], 409);
        } else {
            sendJsonResponse([
                'success' => false,
                'message' => 'Erreur de base de données lors de l\'inscription'
            ], 500);
        }
    }
}

/**
 * Gère les requêtes PUT pour modifier un étudiant existant
 * 
 * @param PDO $db Connexion à la base de données
 */
function handlePutRequest($db) {
    // Lire les données JSON depuis le corps de la requête
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Données JSON invalides'
        ], 400);
    }
    
    // Vérifier que l'ID ou le matricule est fourni
    if (!isset($input['id']) && !isset($input['matricule'])) {
        sendJsonResponse([
            'success' => false,
            'message' => 'ID ou matricule requis pour la modification'
        ], 400);
    }
    
    try {
        // Construire la requête de mise à jour dynamiquement
        $updateFields = [];
        $params = [];
        
        $allowedFields = ['nom', 'prenom', 'sexe', 'filiere', 'departement', 'niveau', 'annee_academique', 'annee_naissance', 'lieu'];
        
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $updateFields[] = "$field = :$field";
                $params[$field] = cleanInput($input[$field]);
            }
        }
        
        if (empty($updateFields)) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Aucun champ à mettre à jour'
            ], 400);
        }
        
        // Déterminer la condition WHERE
        if (isset($input['id'])) {
            $whereCondition = "id = :id";
            $params['id'] = intval($input['id']);
        } else {
            $whereCondition = "matricule = :matricule";
            $params['matricule'] = cleanInput($input['matricule']);
        }
        
        // Construire et exécuter la requête
        $sql = "UPDATE students SET " . implode(', ', $updateFields) . " WHERE $whereCondition";
        $stmt = $db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                logError("Étudiant modifié avec succès", 'INFO');
                sendJsonResponse([
                    'success' => true,
                    'message' => 'Étudiant modifié avec succès'
                ]);
            } else {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Étudiant non trouvé ou aucun changement effectué'
                ], 404);
            }
        } else {
            sendJsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la modification'
            ], 500);
        }
        
    } catch (PDOException $e) {
        logError("Erreur PDO lors de la modification: " . $e->getMessage());
        sendJsonResponse([
            'success' => false,
            'message' => 'Erreur de base de données lors de la modification'
        ], 500);
    }
}
?>