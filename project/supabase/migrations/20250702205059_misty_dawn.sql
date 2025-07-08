-- Base de données pour le système de paiement étudiant
-- Créer d'abord la base de données: CREATE DATABASE student_payment_db;

CREATE DATABASE IF NOT EXISTS student_payment_db;
USE student_payment_db;

-- Table des étudiants
CREATE TABLE IF NOT EXISTS etudiants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    sexe ENUM('M', 'F') NOT NULL,
    filiere VARCHAR(100) NOT NULL,
    departement VARCHAR(100) NOT NULL,
    matricule VARCHAR(50) UNIQUE NOT NULL,
    niveau VARCHAR(50) NOT NULL,
    annee_academique VARCHAR(20) NOT NULL,
    annee_naissance INT NOT NULL,
    lieu VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des paiements
CREATE TABLE IF NOT EXISTS paiements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    etudiant_id INT NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    tranche INT NOT NULL CHECK (tranche IN (1, 2)),
    filiere VARCHAR(100) NOT NULL,
    faculte VARCHAR(100),
    universite VARCHAR(100),
    annee_academique VARCHAR(20) NOT NULL,
    statut ENUM('en_attente', 'valide', 'rejete') DEFAULT 'en_attente',
    date_paiement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_validation TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tranche_payment (etudiant_id, tranche)
);

-- Insertion de données de test
INSERT INTO etudiants (nom, prenom, sexe, filiere, departement, matricule, niveau, annee_academique, annee_naissance, lieu, password) VALUES
('KOUAME', 'Jean', 'M', 'Informatique', 'Sciences et Technologies', 'ETU001', 'L3', '2024-2025', 1998, 'Abidjan', 'password123'),
('TRAORE', 'Marie', 'F', 'Gestion', 'Sciences Economiques', 'ETU002', 'L2', '2024-2025', 1999, 'Bouaké', 'password123'),
('KONE', 'David', 'M', 'Médecine', 'Sciences de la Santé', 'ETU003', 'L1', '2024-2025', 2000, 'Yamoussoukro', 'password123'),
('OUATTARA', 'Aminata', 'F', 'Droit', 'Sciences Juridiques', 'ETU004', 'M1', '2024-2025', 1997, 'San Pedro', 'password123'),
('DIALLO', 'Ibrahim', 'M', 'Informatique', 'Sciences et Technologies', 'ETU005', 'L3', '2024-2025', 1998, 'Daloa', 'password123');

-- Insertion de quelques paiements de test
INSERT INTO paiements (etudiant_id, montant, tranche, filiere, faculte, universite, annee_academique, statut) VALUES
(1, 25000, 1, 'Informatique', 'Sciences et Technologies', 'Université Centrale', '2024-2025', 'en_attente'),
(2, 25000, 1, 'Gestion', 'Sciences Economiques', 'Université Centrale', '2024-2025', 'valide'),
(2, 25000, 2, 'Gestion', 'Sciences Economiques', 'Université Centrale', '2024-2025', 'en_attente'),
(3, 25000, 1, 'Médecine', 'Sciences de la Santé', 'Université Centrale', '2024-2025', 'valide');

-- Index pour améliorer les performances
CREATE INDEX idx_matricule ON etudiants(matricule);
CREATE INDEX idx_etudiant_paiement ON paiements(etudiant_id);
CREATE INDEX idx_statut_paiement ON paiements(statut);
CREATE INDEX idx_tranche ON paiements(tranche);