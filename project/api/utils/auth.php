<?php
/**
 * Utilitaires d'authentification
 * Fonctions pour vérifier les tokens et permissions
 */

/**
 * Vérifie l'authentification d'un étudiant
 * @return int|false ID de l'étudiant ou false si non authentifié
 */
function verifyStudentAuth() {
    $headers = getAuthHeaders();
    
    if (!$headers || !isset($headers['Authorization'])) {
        return false;
    }

    // Extraction du token
    $auth_header = $headers['Authorization'];
    $token = str_replace('Bearer ', '', $auth_header);
    
    if (empty($token)) {
        return false;
    }

    try {
        // Décodage du token simple (en production, utiliser JWT)
        $decoded = base64_decode($token);
        $parts = explode(':', $decoded);
        
        if (count($parts) >= 2 && is_numeric($parts[0])) {
            return (int)$parts[0]; // Retourne l'ID étudiant
        }
    } catch (Exception $e) {
        error_log("Erreur de décodage token: " . $e->getMessage());
    }

    return false;
}

/**
 * Vérifie l'authentification d'un administrateur
 * @return bool True si authentifié comme admin
 */
function verifyAdminAuth() {
    $headers = getAuthHeaders();
    
    if (!$headers || !isset($headers['Authorization'])) {
        return false;
    }

    // Extraction du token
    $auth_header = $headers['Authorization'];
    $token = str_replace('Bearer ', '', $auth_header);
    
    if (empty($token)) {
        return false;
    }

    try {
        // Décodage du token admin
        $decoded = base64_decode($token);
        $parts = explode(':', $decoded);
        
        if (count($parts) >= 2 && $parts[0] === 'admin') {
            return true;
        }
    } catch (Exception $e) {
        error_log("Erreur de décodage token admin: " . $e->getMessage());
    }

    return false;
}

/**
 * Récupère les en-têtes d'autorisation
 * @return array|false
 */
function getAuthHeaders() {
    $headers = array();
    
    // Récupération de l'en-tête Authorization
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers['Authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    } elseif (function_exists('apache_request_headers')) {
        $request_headers = apache_request_headers();
        if (isset($request_headers['Authorization'])) {
            $headers['Authorization'] = $request_headers['Authorization'];
        }
    }
    
    return !empty($headers) ? $headers : false;
}
?>