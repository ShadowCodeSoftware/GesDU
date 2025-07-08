# Système de Paiement Étudiant

Une application complète de gestion des paiements universitaires avec React Native/Expo et API PHP/MySQL.

## 🚀 Fonctionnalités

### Pour les Étudiants
- **Connexion sécurisée** avec matricule et mot de passe
- **Tableau de bord** avec informations personnelles et statut de paiement
- **Paiements en ligne** pour les deux tranches (25,000 FCFA chacune)
- **Historique des paiements** et suivi du statut
- **Profil détaillé** avec informations académiques

### Pour les Administrateurs
- **Dashboard administrateur** avec statistiques complètes
- **Gestion des étudiants** avec liste complète et filtres
- **Validation des paiements** (approbation/rejet)
- **Suivi financier** avec rapports détaillés
- **Interface de gestion** intuitive et moderne

## 📱 Technologies Utilisées

### Frontend (App Mobile)
- **React Native** avec **Expo SDK 52**
- **Expo Router** pour la navigation
- **TypeScript** pour la sécurité des types
- **Lucide React Native** pour les icônes
- **Expo Secure Store** pour le stockage sécurisé
- **Google Fonts Inter** pour la typographie

### Backend (API)
- **PHP 8+** avec architecture MVC
- **MySQL** comme base de données
- **PDO** pour les requêtes sécurisées
- **CORS** configuré pour React Native
- **Authentication** avec tokens Bearer

## 🛠 Installation et Configuration

### Prérequis
- **Node.js** 18+ 
- **npm** ou **yarn**
- **PHP** 8.0+
- **MySQL** 8.0+
- **XAMPP/WAMP** ou serveur local
- **Expo CLI** (`npm install -g @expo/cli`)

### Installation de l'Application Mobile

1. **Installer les dépendances**
```bash
npm install
```

2. **Démarrer l'application**
```bash
npx expo start
```

3. **Scanner le QR code** avec l'app Expo Go ou utiliser un émulateur

### Configuration de l'API PHP

1. **Démarrer XAMPP/WAMP** et activer Apache + MySQL

2. **Créer la base de données**
```sql
-- Dans phpMyAdmin, exécuter le fichier database/schema.sql
CREATE DATABASE student_payment_db;
```

3. **Configurer la connexion** dans `api/config/database.php`
```php
private $host = "localhost";
private $db_name = "student_payment_db";
private $username = "root";
private $password = "";
```

4. **Copier les fichiers API** dans le dossier `htdocs/student-payment-api/`

5. **Tester l'API** : `http://localhost/student-payment-api/`

## 📊 Structure de la Base de Données

### Table `etudiants`
- **id** : Identifiant unique
- **nom, prenom** : Nom et prénom
- **sexe** : M/F
- **filiere, departement** : Informations académiques
- **matricule** : Numéro étudiant unique
- **niveau** : L1, L2, L3, M1, M2
- **annee_academique** : Année d'études
- **annee_naissance, lieu** : Informations personnelles
- **password** : Mot de passe (à hasher en production)

### Table `paiements`
- **id** : Identifiant unique
- **etudiant_id** : Référence vers l'étudiant
- **montant** : 25,000 FCFA par tranche
- **tranche** : 1 ou 2
- **statut** : en_attente, valide, rejete
- **date_paiement, date_validation** : Horodatage

## 🔐 Authentification

### Étudiants
- **Matricule** : ETU001, ETU002, etc.
- **Mot de passe** : password123 (pour les tests)

### Administrateur
- **Identifiant** : admin
- **Mot de passe** : admin

## 🎯 Utilisation

### Côté Étudiant
1. **Se connecter** avec son matricule
2. **Consulter** le tableau de bord
3. **Effectuer un paiement** pour chaque tranche
4. **Suivre** le statut de validation
5. **Voir** son profil et historique

### Côté Administrateur
1. **Se connecter** avec les identifiants admin
2. **Consulter** les statistiques globales
3. **Gérer** la liste des étudiants
4. **Valider/Rejeter** les paiements en attente
5. **Suivre** les performances financières

## 📁 Structure du Projet

```
├── app/                          # Application React Native
│   ├── (auth)/                   # Pages d'authentification
│   ├── (student-tabs)/           # Onglets étudiants
│   ├── (admin-tabs)/             # Onglets administrateur
│   └── index.tsx                 # Page d'accueil
├── api/                          # API PHP
│   ├── auth/                     # Authentification
│   ├── admin/                    # Endpoints admin
│   ├── student/                  # Endpoints étudiant
│   ├── payment/                  # Gestion paiements
│   ├── models/                   # Modèles de données
│   ├── config/                   # Configuration
│   └── utils/                    # Utilitaires
├── services/                     # Services API (TypeScript)
├── database/                     # Scripts SQL
└── README.md                     # Documentation
```

## 🎨 Design et UX

- **Design moderne** avec couleurs cohérentes
- **Navigation intuitive** par onglets
- **Feedback visuel** pour toutes les actions
- **Responsive** adapté aux mobiles
- **Animations subtiles** pour une meilleure UX
- **États de chargement** et gestion d'erreurs

## 🔧 Personnalisation

### Modifier l'URL de l'API
Dans les fichiers `services/*.ts`, changer :
```typescript
private baseUrl = 'http://YOUR_SERVER/student-payment-api';
```

### Ajouter des champs étudiants
1. Modifier la base de données
2. Mettre à jour le modèle `Student.php`
3. Adapter les interfaces TypeScript
4. Mettre à jour les formulaires

### Changer les montants
Dans `api/payment/make-payment.php` :
```php
if ($data->montant != 25000) { // Changer ici
```

## 🚀 Déploiement

### Application Mobile
```bash
# Build pour production
expo build:android
expo build:ios

# Ou utiliser EAS Build
eas build --platform android
```

### API PHP
1. **Uploader** les fichiers sur votre serveur
2. **Configurer** la base de données
3. **Mettre à jour** les URLs dans l'app
4. **Activer HTTPS** en production

## 🔒 Sécurité

- **Tokens Bearer** pour l'authentification
- **Validation** de toutes les entrées
- **Protection CORS** configurée
- **Échappement SQL** avec PDO
- **Stockage sécurisé** avec Expo Secure Store

## 📞 Support

Pour toute question ou problème :
1. Vérifier la documentation
2. Tester avec les données de demo
3. Vérifier les logs d'erreur
4. S'assurer que l'API est accessible

## 📝 Licence

Ce projet est sous licence MIT. Libre d'utilisation et de modification.