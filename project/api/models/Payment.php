<?php
/**
 * Modèle Payment
 * Gère les opérations liées aux paiements
 */

class Payment {
    private $conn;
    private $table_name = "paiements";

    // Propriétés du paiement
    public $id;
    public $etudiant_id;
    public $montant;
    public $tranche;
    public $filiere;
    public $faculte;
    public $universite;
    public $annee_academique;
    public $statut;
    public $date_paiement;
    public $date_validation;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crée un nouveau paiement
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET etudiant_id=:etudiant_id, montant=:montant, tranche=:tranche,
                      filiere=:filiere, faculte=:faculte, universite=:universite,
                      annee_academique=:annee_academique, statut='en_attente'";

        $stmt = $this->conn->prepare($query);

        // Nettoyage des données
        $this->filiere = htmlspecialchars(strip_tags($this->filiere));
        $this->faculte = htmlspecialchars(strip_tags($this->faculte));
        $this->universite = htmlspecialchars(strip_tags($this->universite));

        // Liaison des paramètres
        $stmt->bindParam(":etudiant_id", $this->etudiant_id);
        $stmt->bindParam(":montant", $this->montant);
        $stmt->bindParam(":tranche", $this->tranche);
        $stmt->bindParam(":filiere", $this->filiere);
        $stmt->bindParam(":faculte", $this->faculte);
        $stmt->bindParam(":universite", $this->universite);
        $stmt->bindParam(":annee_academique", $this->annee_academique);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Récupère les paiements en attente avec les informations étudiants
     */
    public function getPendingPayments() {
        $query = "SELECT p.*, CONCAT(e.prenom, ' ', e.nom) as student_name, e.matricule
                  FROM " . $this->table_name . " p
                  INNER JOIN etudiants e ON p.etudiant_id = e.id
                  WHERE p.statut = 'en_attente'
                  ORDER BY p.date_paiement DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les paiements validés
     */
    public function getValidatedPayments() {
        $query = "SELECT p.*, CONCAT(e.prenom, ' ', e.nom) as student_name, e.matricule
                  FROM " . $this->table_name . " p
                  INNER JOIN etudiants e ON p.etudiant_id = e.id
                  WHERE p.statut = 'valide'
                  ORDER BY p.date_validation DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Valide ou rejette un paiement
     */
    public function validatePayment($payment_id, $action) {
        $statut = ($action === 'approve') ? 'valide' : 'rejete';
        
        $query = "UPDATE " . $this->table_name . "
                  SET statut = :statut, date_validation = NOW()
                  WHERE id = :payment_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":statut", $statut);
        $stmt->bindParam(":payment_id", $payment_id);

        return $stmt->execute();
    }

    /**
     * Récupère l'historique des paiements d'un étudiant
     */
    public function getStudentHistory($student_id) {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE etudiant_id = :student_id
                  ORDER BY date_paiement DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Vérifie si un paiement existe déjà pour une tranche
     */
    public function checkExistingPayment($student_id, $tranche) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . "
                  WHERE etudiant_id = :student_id AND tranche = :tranche 
                  AND statut IN ('en_attente', 'valide')";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->bindParam(":tranche", $tranche);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    /**
     * Récupère les statistiques de paiement
     */
    public function getPaymentStats() {
        $query = "SELECT 
                    COUNT(DISTINCT etudiant_id) as total_students,
                    COUNT(CASE WHEN statut = 'valide' THEN 1 END) as total_payments,
                    COUNT(CASE WHEN statut = 'en_attente' THEN 1 END) as pending_payments,
                    SUM(CASE WHEN statut = 'valide' THEN montant ELSE 0 END) as paid_amount,
                    (SELECT COUNT(*) FROM etudiants) * 50000 as total_amount
                  FROM " . $this->table_name;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>