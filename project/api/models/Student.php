<?php
/**
 * Modèle Student
 * Gère les opérations liées aux étudiants
 */

class Student {
    private $conn;
    private $table_name = "etudiants";

    // Propriétés de l'étudiant
    public $id;
    public $nom;
    public $prenom;
    public $sexe;
    public $filiere;
    public $departement;
    public $matricule;
    public $niveau;
    public $annee_academique;
    public $annee_naissance;
    public $lieu;
    public $password;
    public $date_inscription;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Authentification d'un étudiant
     */
    public function login($matricule, $password) {
        $query = "SELECT id, nom, prenom, sexe, filiere, departement, matricule, 
                         niveau, annee_academique, annee_naissance, lieu, date_inscription
                  FROM " . $this->table_name . " 
                  WHERE matricule = :matricule AND password = :password";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":matricule", $matricule);
        // En production, utiliser password_verify() avec des mots de passe hachés
        $stmt->bindParam(":password", $password);

        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return false;
    }

    /**
     * Récupère un étudiant par son ID
     */
    public function getById($id) {
        $query = "SELECT id, nom, prenom, sexe, filiere, departement, matricule, 
                         niveau, annee_academique, annee_naissance, lieu, date_inscription
                  FROM " . $this->table_name . " 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return false;
    }

    /**
     * Récupère le statut de paiement d'un étudiant
     */
    public function getPaymentStatus($student_id) {
        $query = "SELECT 
                    SUM(CASE WHEN tranche = 1 AND statut = 'valide' THEN 1 ELSE 0 END) as tranche1,
                    SUM(CASE WHEN tranche = 2 AND statut = 'valide' THEN 1 ELSE 0 END) as tranche2
                  FROM paiements 
                  WHERE etudiant_id = :student_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return array(
            'tranche1' => $result['tranche1'] > 0,
            'tranche2' => $result['tranche2'] > 0
        );
    }

    /**
     * Récupère tous les étudiants avec leur statut de paiement
     */
    public function getAllWithPaymentStatus() {
        $query = "SELECT e.*, 
                    SUM(CASE WHEN p.tranche = 1 AND p.statut = 'valide' THEN 1 ELSE 0 END) as tranche1_payee,
                    SUM(CASE WHEN p.tranche = 2 AND p.statut = 'valide' THEN 1 ELSE 0 END) as tranche2_payee
                  FROM " . $this->table_name . " e
                  LEFT JOIN paiements p ON e.id = p.etudiant_id
                  GROUP BY e.id
                  ORDER BY e.nom, e.prenom";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un nouvel étudiant
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET nom=:nom, prenom=:prenom, sexe=:sexe, filiere=:filiere, 
                      departement=:departement, matricule=:matricule, niveau=:niveau,
                      annee_academique=:annee_academique, annee_naissance=:annee_naissance,
                      lieu=:lieu, password=:password";

        $stmt = $this->conn->prepare($query);

        // Nettoyage des données
        $this->nom = htmlspecialchars(strip_tags($this->nom));
        $this->prenom = htmlspecialchars(strip_tags($this->prenom));
        $this->matricule = htmlspecialchars(strip_tags($this->matricule));

        // Liaison des paramètres
        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":prenom", $this->prenom);
        $stmt->bindParam(":sexe", $this->sexe);
        $stmt->bindParam(":filiere", $this->filiere);
        $stmt->bindParam(":departement", $this->departement);
        $stmt->bindParam(":matricule", $this->matricule);
        $stmt->bindParam(":niveau", $this->niveau);
        $stmt->bindParam(":annee_academique", $this->annee_academique);
        $stmt->bindParam(":annee_naissance", $this->annee_naissance);
        $stmt->bindParam(":lieu", $this->lieu);
        $stmt->bindParam(":password", $this->password);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}
?>