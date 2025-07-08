<?php
/**
 * Configuration de la base de données pour le système de paiement étudiant
 * 
 * Ce fichier contient toutes les configurations nécessaires pour la connexion
 * à la base de données MySQL ainsi que les paramètres de l'application.
 */

// Configuration de la base de données
define('DB_HOST', 'localhost');         // Hôte de la base de données
define('DB_NAME', 'student_payment_db'); // Nom de la base de données
define('DB_USER', 'root');              // Nom d'utilisateur MySQL
define('DB_PASS', '');                  // Mot de passe MySQL (vide pour XAMPP par défaut)
define('DB_CHARSET', 'utf8mb4');        // Jeu de caractères pour supporter les caractères spéciaux

// Paramètres de l'application
define('PAYMENT_AMOUNT', 25000);        // Montant par tranche en FCFA
define('TOTAL_TRANCHES', 2);            // Nombre total de tranches
define('TOTAL_AMOUNT', PAYMENT_AMOUNT * TOTAL_TRANCHES); // Montant total à payer

// Configuration CORS pour permettre les requêtes depuis l'application mobile
define('CORS_ORIGIN', '*');             // Origines autorisées (* pour toutes)
define('CORS_METHODS', 'GET, POST, PUT, DELETE, OPTIONS'); // Méthodes HTTP autorisées
define('CORS_HEADERS', 'Content-Type, Authorization, X-Requested-With'); // En-têtes autorisés

// Configuration des identifiants administrateur par défaut
define('ADMIN_USERNAME', 'admi');       // Nom d'utilisateur administrateur
define('ADMIN_PASSWORD', 'admi');       // Mot de passe administrateur

// Fuseau horaire
date_default_timezone_set('Africa/Abidjan'); // Timezone pour la Côte d'Ivoire

/**
 * Classe de gestion de la base de données
 * 
 * Cette classe utilise PDO pour une connexion sécurisée à la base de données
 * avec gestion des erreurs et des requêtes préparées.
 */
class Database {
    private $connection;
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $charset = DB_CHARSET;

    /**
     * Établit la connexion à la base de données
     * 
     * @return PDO|null Objet PDO ou null en cas d'erreur
     */
    public function getConnection() {
        $this->connection = null;

        try {
            // Construction du DSN (Data Source Name)
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            
            // Options PDO pour une connexion sécurisée
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,    // Gestion des erreurs par exception
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,          // Mode de récupération par défaut
                PDO::ATTR_EMULATE_PREPARES   => false,                     // Utiliser les vraies requêtes préparées
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . $this->charset // Définir le charset
            ];

            // Création de la connexion PDO
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $exception) {
            // Log de l'erreur de connexion
            error_log("Erreur de connexion à la base de données: " . $exception->getMessage());
            return null;
        }

        return $this->connection;
    }

    /**
     * Ferme la connexion à la base de données
     */
    public function closeConnection() {
        $this->connection = null;
    }

    /**
     * Teste la connexion à la base de données
     * 
     * @return bool True si la connexion est réussie, false sinon
     */
    public function testConnection() {
        $connection = $this->getConnection();
        if ($connection !== null) {
            $this->closeConnection();
            return true;
        }
        return false;
    }
}

/**
 * Fonction utilitaire pour définir les en-têtes CORS
 * 
 * Cette fonction permet de gérer les requêtes cross-origin depuis l'application mobile
 */
function setCorsHeaders() {
    // Permettre les requêtes depuis toutes les origines (à ajuster en production)
    header("Access-Control-Allow-Origin: " . CORS_ORIGIN);
    
    // Méthodes HTTP autorisées
    header("Access-Control-Allow-Methods: " . CORS_METHODS);
    
    // En-têtes autorisés
    header("Access-Control-Allow-Headers: " . CORS_HEADERS);
    
    // Durée de mise en cache des options CORS (en secondes)
    header("Access-Control-Max-Age: 3600");
    
    // Permettre l'envoi des cookies avec les requêtes CORS
    header("Access-Control-Allow-Credentials: true");
}

/**
 * Fonction utilitaire pour envoyer une réponse JSON
 * 
 * @param array $data Données à retourner
 * @param int $httpCode Code de statut HTTP
 */
function sendJsonResponse($data, $httpCode = 200) {
    // Définir le type de contenu
    header('Content-Type: application/json; charset=utf-8');
    
    // Définir le code de statut HTTP
    http_response_code($httpCode);
    
    // Encoder et envoyer la réponse
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Fonction utilitaire pour valider et nettoyer les données d'entrée
 * 
 * @param string $data Données à nettoyer
 * @return string Données nettoyées
 */
function cleanInput($data) {
    // Supprimer les espaces en début et fin
    $data = trim($data);
    
    // Supprimer les antislashs
    $data = stripslashes($data);
    
    // Convertir les caractères spéciaux en entités HTML
    $data = htmlspecialchars($data);
    
    return $data;
}

/**
 * Fonction utilitaire pour valider un matricule
 * 
 * @param string $matricule Matricule à valider
 * @return bool True si le matricule est valide
 */
function validateMatricule($matricule) {
    // Le matricule doit contenir au moins 3 caractères et être alphanumérique
    return preg_match('/^[A-Z0-9]{3,}$/', strtoupper($matricule));
}

/**
 * Fonction utilitaire pour valider un montant de paiement
 * 
 * @param float $montant Montant à valider
 * @return bool True si le montant est valide (25000 FCFA)
 */
function validatePaymentAmount($montant) {
    return floatval($montant) === floatval(PAYMENT_AMOUNT);
}

/**
 * Fonction utilitaire pour valider une tranche de paiement
 * 
 * @param int $tranche Numéro de tranche à valider
 * @return bool True si la tranche est valide (1 ou 2)
 */
function validateTranche($tranche) {
    return in_array(intval($tranche), [1, 2]);
}

/**
 * Fonction pour logger les erreurs dans un fichier
 * 
 * @param string $message Message d'erreur
 * @param string $level Niveau d'erreur (ERROR, WARNING, INFO)
 */
function logError($message, $level = 'ERROR') {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
    
    // Écrire dans le fichier de log (créer le dossier logs s'il n'existe pas)
    if (!file_exists('logs')) {
        mkdir('logs', 0755, true);
    }
    
    file_put_contents('logs/app.log', $logMessage, FILE_APPEND | LOCK_EX);
}

// Définir les en-têtes CORS au chargement du fichier
setCorsHeaders();

// Gérer les requêtes OPTIONS (pre-flight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
?>