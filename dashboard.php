<?php
/**
 * API pour le tableau de bord
 * 
 * Ce fichier fournit les données pour le tableau de bord :
 * - Statistiques générales
 * - Liste des étudiants avec leur statut de paiement
 * - Données pour les graphiques et rapports
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

// Récupérer la méthode HTTP (seul GET est autorisé)
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    sendJsonResponse([
        'success' => false,
        'message' => 'Seule la méthode GET est autorisée'
    ], 405);
}

try {
    $action = isset($_GET['action']) ? cleanInput($_GET['action']) : '';
    
    switch ($action) {
        case 'stats':
            getDashboardStats($db);
            break;
            
        case 'students':
            getStudentsWithPaymentStatus($db);
            break;
            
        case 'monthly_stats':
            getMonthlyStats($db);
            break;
            
        case 'filiere_stats':
            getFiliereStats($db);
            break;
            
        default:
            sendJsonResponse([
                'success' => false,
                'message' => 'Action non spécifiée. Actions disponibles: stats, students, monthly_stats, filiere_stats'
            ], 400);
            break;
    }
} catch (Exception $e) {
    logError("Erreur dans dashboard.php: " . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'message' => 'Erreur interne du serveur'
    ], 500);
}

/**
 * Récupère les statistiques générales du tableau de bord
 * 
 * @param PDO $db Connexion à la base de données
 */
function getDashboardStats($db) {
    try {
        // Utiliser la vue créée dans la base de données
        $sql = "SELECT * FROM dashboard_stats";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        
        $stats = $stmt->fetch();
        
        if (!$stats) {
            // Si la vue n'existe pas, calculer manuellement
            $manualStatsSql = "SELECT 
                                (SELECT COUNT(*) FROM students) as total_students,
                                (SELECT COUNT(*) FROM payments WHERE statut = 'valide') as total_payments,
                                (SELECT COALESCE(SUM(montant), 0) FROM payments WHERE statut = 'valide') as total_amount,
                                (SELECT COUNT(*) FROM payments WHERE statut = 'en_attente') as pending_payments";
            
            $manualStmt = $db->prepare($manualStatsSql);
            $manualStmt->execute();
            $stats = $manualStmt->fetch();
        }
        
        // Statistiques additionnelles
        $additionalSql = "SELECT 
                            (SELECT COUNT(DISTINCT matricule) FROM payments WHERE statut = 'valide') as students_who_paid,
                            (SELECT COUNT(*) FROM payments WHERE statut = 'rejete') as rejected_payments,
                            (SELECT AVG(montant) FROM payments WHERE statut = 'valide') as average_payment";
        
        $additionalStmt = $db->prepare($additionalSql);
        $additionalStmt->execute();
        $additional = $additionalStmt->fetch();
        
        // Combiner les statistiques
        $combinedStats = array_merge($stats, $additional);
        
        sendJsonResponse([
            'success' => true,
            'data' => $combinedStats,
            'message' => 'Statistiques du tableau de bord récupérées avec succès'
        ]);
        
    } catch (PDOException $e) {
        logError("Erreur lors de la récupération des statistiques: " . $e->getMessage());
        sendJsonResponse([
            'success' => false,
            'message' => 'Erreur lors de la récupération des statistiques'
        ], 500);
    }
}

/**
 * Récupère la liste des étudiants avec leur statut de paiement
 * 
 * @param PDO $db Connexion à la base de données
 */
function getStudentsWithPaymentStatus($db) {
    try {
        // Utiliser la vue si elle existe, sinon requête manuelle
        $sql = "SELECT * FROM student_payment_summary ORDER BY nom, prenom";
        
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $students = $stmt->fetchAll();
        } catch (PDOException $e) {
            // Si la vue n'existe pas, faire la requête manuellement
            $manualSql = "SELECT 
                            s.id,
                            s.nom,
                            s.prenom,
                            s.matricule,
                            s.filiere,
                            s.niveau,
                            s.annee_academique,
                            COALESCE(SUM(CASE WHEN p.statut = 'valide' THEN p.montant ELSE 0 END), 0) as total_paye,
                            (" . TOTAL_AMOUNT . " - COALESCE(SUM(CASE WHEN p.statut = 'valide' THEN p.montant ELSE 0 END), 0)) as total_restant,
                            CASE 
                                WHEN COALESCE(SUM(CASE WHEN p.statut = 'valide' THEN p.montant ELSE 0 END), 0) = " . TOTAL_AMOUNT . " THEN 'complet'
                                WHEN COALESCE(SUM(CASE WHEN p.statut = 'valide' THEN p.montant ELSE 0 END), 0) > 0 THEN 'partiel'
                                ELSE 'non_paye'
                            END as statut_paiement,
                            MAX(p.date_paiement) as derniere_activite
                          FROM students s
                          LEFT JOIN payments p ON s.id = p.student_id AND s.annee_academique = p.annee_academique
                          GROUP BY s.id, s.nom, s.prenom, s.matricule, s.filiere, s.niveau, s.annee_academique
                          ORDER BY s.nom, s.prenom";
            
            $stmt = $db->prepare($manualSql);
            $stmt->execute();
            $students = $stmt->fetchAll();
        }
        
        // Filtrer par statut si spécifié
        if (isset($_GET['statut']) && !empty($_GET['statut'])) {
            $statusFilter = cleanInput($_GET['statut']);
            $students = array_filter($students, function($student) use ($statusFilter) {
                return $student['statut_paiement'] === $statusFilter;
            });
            $students = array_values($students); // Réindexer le tableau
        }
        
        // Pagination
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = isset($_GET['limit']) ? min(100, max(10, intval($_GET['limit']))) : 50;
        $offset = ($page - 1) * $limit;
        
        $totalStudents = count($students);
        $paginatedStudents = array_slice($students, $offset, $limit);
        
        sendJsonResponse([
            'success' => true,
            'data' => $paginatedStudents,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $totalStudents,
                'total_pages' => ceil($totalStudents / $limit)
            ],
            'message' => 'Liste des étudiants avec statut de paiement récupérée avec succès'
        ]);
        
    } catch (PDOException $e) {
        logError("Erreur lors de la récupération des étudiants: " . $e->getMessage());
        sendJsonResponse([
            'success' => false,
            'message' => 'Erreur lors de la récupération des étudiants'
        ], 500);
    }
}

/**
 * Récupère les statistiques mensuelles pour les graphiques
 * 
 * @param PDO $db Connexion à la base de données
 */
function getMonthlyStats($db) {
    try {
        $year = isset($_GET['annee']) ? intval($_GET['annee']) : date('Y');
        
        $sql = "SELECT 
                    MONTH(date_paiement) as mois,
                    MONTHNAME(date_paiement) as nom_mois,
                    COUNT(*) as nombre_paiements,
                    SUM(montant) as montant_total
                FROM payments 
                WHERE YEAR(date_paiement) = :year 
                    AND statut = 'valide'
                GROUP BY MONTH(date_paiement), MONTHNAME(date_paiement)
                ORDER BY mois";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        
        $monthlyStats = $stmt->fetchAll();
        
        // Créer un tableau avec tous les mois (même ceux sans paiements)
        $months = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];
        
        $completeStats = [];
        foreach ($months as $num => $name) {
            $found = false;
            foreach ($monthlyStats as $stat) {
                if ($stat['mois'] == $num) {
                    $completeStats[] = $stat;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $completeStats[] = [
                    'mois' => $num,
                    'nom_mois' => $name,
                    'nombre_paiements' => 0,
                    'montant_total' => 0
                ];
            }
        }
        
        sendJsonResponse([
            'success' => true,
            'data' => [
                'annee' => $year,
                'statistiques' => $completeStats
            ],
            'message' => 'Statistiques mensuelles récupérées avec succès'
        ]);
        
    } catch (PDOException $e) {
        logError("Erreur lors de la récupération des statistiques mensuelles: " . $e->getMessage());
        sendJsonResponse([
            'success' => false,
            'message' => 'Erreur lors de la récupération des statistiques mensuelles'
        ], 500);
    }
}

/**
 * Récupère les statistiques par filière
 * 
 * @param PDO $db Connexion à la base de données
 */
function getFiliereStats($db) {
    try {
        $sql = "SELECT 
                    p.filiere,
                    COUNT(DISTINCT p.matricule) as etudiants_payants,
                    COUNT(*) as total_paiements,
                    SUM(p.montant) as montant_total,
                    AVG(p.montant) as montant_moyen,
                    (SELECT COUNT(*) FROM students s WHERE s.filiere = p.filiere) as total_etudiants_filiere
                FROM payments p 
                WHERE p.statut = 'valide'
                GROUP BY p.filiere
                ORDER BY montant_total DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        
        $filiereStats = $stmt->fetchAll();
        
        // Calculer le pourcentage de paiement par filière
        foreach ($filiereStats as &$stat) {
            if ($stat['total_etudiants_filiere'] > 0) {
                $stat['taux_paiement'] = round(($stat['etudiants_payants'] / $stat['total_etudiants_filiere']) * 100, 2);
            } else {
                $stat['taux_paiement'] = 0;
            }
        }
        
        sendJsonResponse([
            'success' => true,
            'data' => $filiereStats,
            'message' => 'Statistiques par filière récupérées avec succès'
        ]);
        
    } catch (PDOException $e) {
        logError("Erreur lors de la récupération des statistiques par filière: " . $e->getMessage());
        sendJsonResponse([
            'success' => false,
            'message' => 'Erreur lors de la récupération des statistiques par filière'
        ], 500);
    }
}
?>