<?php
/**
 * Configuration de la base de données
 * Connexion MySQL avec PDO pour plus de sécurité
 */

class Database {
    // Paramètres de connexion à la base de données
    private $host = "localhost";
    private $db_name = "student_payment_db";
    private $username = "root";
    private $password = "";
    public $conn;

    /**
     * Établit la connexion à la base de données
     * @return PDO|null
     */
    public function getConnection() {
        $this->conn = null;

        try {
            // Création de la connexion PDO avec options de sécurité
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                )
            );
        } catch(PDOException $exception) {
            echo "Erreur de connexion: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>