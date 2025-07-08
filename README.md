# API de Gestion des Paiements Étudiants

## Description

Cette API REST permet de gérer un système de paiement pour les étudiants universitaires. Elle gère les inscriptions, les paiements en deux tranches de 25 000 FCFA chacune, et l'administration des validations de paiements.

## Structure des fichiers

```
api/
├── config.php          # Configuration de la base de données et fonctions utilitaires
├── database.sql        # Script de création de la base de données
├── students.php        # API pour la gestion des étudiants
├── payments.php        # API pour la gestion des paiements
├── admin.php          # API pour l'administration bancaire
├── dashboard.php      # API pour le tableau de bord
└── README.md          # Cette documentation
```

## Installation

### Prérequis

- Serveur web (Apache/Nginx)
- PHP 7.4 ou supérieur avec PDO MySQL
- MySQL 5.7 ou supérieur
- XAMPP, WAMP, ou LAMP (recommandé pour le développement)

### Étapes d'installation

1. **Copier les fichiers API**
   ```bash
   # Copier le dossier api dans votre répertoire web
   cp -r api/ /var/www/html/student-payment-api/
   # ou pour XAMPP
   cp -r api/ C:/xampp/htdocs/student-payment-api/
   ```

2. **Créer la base de données**
   ```sql
   -- Ouvrir phpMyAdmin ou MySQL Workbench
   -- Exécuter le contenu du fichier database.sql
   ```

3. **Configurer la connexion**
   ```php
   // Modifier config.php si nécessaire
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'student_payment_db');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // Mot de passe MySQL
   ```

4. **Tester l'installation**
   ```bash
   # Accéder à l'URL de test
   http://localhost/student-payment-api/students.php
   ```

## Utilisation de l'API

### Base URL
```
http://localhost/student-payment-api/
```

### Authentification Admin
```
Identifiant: admi
Mot de passe: admi
```

## Endpoints disponibles

### 1. Gestion des Étudiants (`students.php`)

#### Inscrire un étudiant
```http
POST /students.php
Content-Type: application/json

{
    "nom": "KOUAKOU",
    "prenom": "Jean",
    "sexe": "M",
    "filiere": "Informatique",
    "departement": "Sciences et Technologies",
    "matricule": "INF2024001",
    "niveau": "L3",
    "annee_academique": "2024-2025",
    "annee_naissance": "2001",
    "lieu": "Abidjan"
}
```

#### Rechercher un étudiant
```http
GET /students.php?matricule=INF2024001
```

#### Lister tous les étudiants
```http
GET /students.php?page=1&limit=50&filiere=Informatique
```

### 2. Gestion des Paiements (`payments.php`)

#### Effectuer un paiement
```http
POST /payments.php
Content-Type: application/json

{
    "matricule": "INF2024001",
    "montant": 25000,
    "filiere": "Informatique",
    "faculte": "Sciences et Technologies",
    "universite": "Université Félix Houphouët-Boigny",
    "annee_academique": "2024-2025",
    "tranche": 1
}
```

#### Consulter les paiements d'un étudiant
```http
GET /payments.php?matricule=INF2024001
```

#### Lister tous les paiements
```http
GET /payments.php?statut=en_attente&tranche=1
```

### 3. Administration (`admin.php`)

#### Récupérer les paiements en attente
```http
GET /admin.php?action=pending_payments
```

#### Valider un paiement
```http
POST /admin.php
Content-Type: application/json

{
    "action": "validate_payment",
    "payment_id": 1,
    "status": "valide",
    "admin": "admi",
    "commentaire": "Paiement validé"
}
```

#### Authentification admin
```http
POST /admin.php
Content-Type: application/json

{
    "action": "login",
    "username": "admi",
    "password": "admi"
}
```

### 4. Tableau de bord (`dashboard.php`)

#### Statistiques générales
```http
GET /dashboard.php?action=stats
```

#### Liste des étudiants avec statut de paiement
```http
GET /dashboard.php?action=students&statut=complet
```

#### Statistiques mensuelles
```http
GET /dashboard.php?action=monthly_stats&annee=2024
```

#### Statistiques par filière
```http
GET /dashboard.php?action=filiere_stats
```

## Codes de réponse

| Code | Description |
|------|-------------|
| 200  | Succès |
| 201  | Créé avec succès |
| 400  | Données invalides |
| 401  | Non autorisé |
| 404  | Non trouvé |
| 409  | Conflit (duplication) |
| 500  | Erreur serveur |

## Format des réponses

### Réponse de succès
```json
{
    "success": true,
    "data": { ... },
    "message": "Opération réussie"
}
```

### Réponse d'erreur
```json
{
    "success": false,
    "message": "Description de l'erreur"
}
```

## Modèle de données

### Étudiant
```json
{
    "id": 1,
    "nom": "KOUAKOU",
    "prenom": "Jean",
    "sexe": "M",
    "filiere": "Informatique",
    "departement": "Sciences et Technologies",
    "matricule": "INF2024001",
    "niveau": "L3",
    "annee_academique": "2024-2025",
    "annee_naissance": 2001,
    "lieu": "Abidjan",
    "date_inscription": "2024-01-15 10:30:00"
}
```

### Paiement
```json
{
    "id": 1,
    "student_id": 1,
    "matricule": "INF2024001",
    "montant": 25000.00,
    "filiere": "Informatique",
    "faculte": "Sciences et Technologies",
    "universite": "Université Félix Houphouët-Boigny",
    "annee_academique": "2024-2025",
    "tranche": 1,
    "statut": "valide",
    "date_paiement": "2024-01-15 11:00:00",
    "date_validation": "2024-01-15 14:00:00",
    "admin_validateur": "admi"
}
```

## Règles métier

1. **Paiement en 2 tranches** : Chaque étudiant doit payer 50 000 FCFA par année académique, répartis en 2 tranches de 25 000 FCFA.

2. **Validation obligatoire** : Tous les paiements doivent être validés par un administrateur avant d'être considérés comme effectifs.

3. **Unicité des paiements** : Un étudiant ne peut payer qu'une seule fois par tranche et par année académique.

4. **Matricule unique** : Chaque étudiant a un matricule unique dans le système.

## Sécurité

- **Validation des données** : Toutes les entrées sont nettoyées et validées
- **Requêtes préparées** : Protection contre l'injection SQL
- **Gestion d'erreurs** : Les erreurs sont loggées sans exposer d'informations sensibles
- **CORS** : Configuration pour permettre les requêtes cross-origin

## Logs

Les logs sont stockés dans le fichier `logs/app.log` avec les niveaux :
- `INFO` : Opérations réussies
- `WARNING` : Tentatives d'accès non autorisées
- `ERROR` : Erreurs techniques

## Tests

### Tester avec curl

```bash
# Inscrire un étudiant
curl -X POST http://localhost/student-payment-api/students.php \
  -H "Content-Type: application/json" \
  -d '{"nom":"TEST","prenom":"Étudiant","matricule":"TEST001","filiere":"Test"}'

# Effectuer un paiement
curl -X POST http://localhost/student-payment-api/payments.php \
  -H "Content-Type: application/json" \
  -d '{"matricule":"TEST001","montant":25000,"filiere":"Test","faculte":"Test","tranche":1}'

# Consulter les paiements
curl http://localhost/student-payment-api/payments.php?matricule=TEST001
```

## Dépannage

### Erreurs courantes

1. **Erreur de connexion à la base de données**
   - Vérifier les paramètres dans `config.php`
   - S'assurer que MySQL est démarré
   - Vérifier les droits d'accès

2. **Erreur CORS**
   - Vérifier que les en-têtes CORS sont correctement définis
   - Utiliser un serveur web plutôt qu'un accès direct aux fichiers

3. **Erreur 404**
   - Vérifier que les fichiers sont dans le bon répertoire
   - Configurer le serveur web pour réécrire les URLs si nécessaire

4. **Erreur de validation**
   - Vérifier le format des données envoyées
   - Consulter les logs pour plus de détails

## Support

Pour toute question ou problème :
1. Consulter les logs dans `logs/app.log`
2. Vérifier la configuration de la base de données
3. Tester les endpoints avec un outil comme Postman
4. Consulter cette documentation

## Licence

Ce projet est développé à des fins éducatives et peut être utilisé librement.