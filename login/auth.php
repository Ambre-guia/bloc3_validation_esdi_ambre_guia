<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../config.php');

/**
 * Vérifie si l'utilisateur est connecté
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur est admin
 * @return bool
 */
function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

/**
 * Authentifie un utilisateur
 * @param string $email
 * @param string $password
 * @return array|bool Retourne les données utilisateur ou false si échec
 */
// Ajouter après les fonctions existantes

/**
 * Vérifie si l'adresse IP a dépassé le nombre maximal de tentatives de connexion
 * @param string $ip L'adresse IP à vérifier
 * @return bool True si l'IP est bloquée, false sinon
 */
function isIPBlocked($ip) {
    global $pdo;
    
    try {
        // Supprimer les anciennes tentatives (plus de 30 minutes)
        $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
        $stmt->execute();
        
        // Vérifier le nombre de tentatives récentes
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM login_attempts WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
        $stmt->execute([$ip]);
        $result = $stmt->fetch();
        
        return $result['count'] >= 5; // Bloquer après 5 tentatives
    } catch (PDOException $e) {
        // Si la table n'existe pas encore, la créer
        if ($e->getCode() == '42S02') {
            createLoginAttemptsTable();
            return false;
        }
        return false;
    }
}

/**
 * Enregistre une tentative de connexion échouée
 * @param string $ip L'adresse IP à enregistrer
 */
function recordFailedLoginAttempt($ip) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO login_attempts (ip_address, attempt_time) VALUES (?, NOW())");
        $stmt->execute([$ip]);
    } catch (PDOException $e) {
        // Si la table n'existe pas encore, la créer
        if ($e->getCode() == '42S02') {
            createLoginAttemptsTable();
            recordFailedLoginAttempt($ip);
        }
    }
}

/**
 * Crée la table login_attempts si elle n'existe pas
 */
function createLoginAttemptsTable() {
    global $pdo;
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        attempt_time DATETIME NOT NULL,
        INDEX (ip_address, attempt_time)
    )");
}

// Modifier la fonction loginUser pour intégrer la protection contre les attaques par force brute
function loginUser($email, $password) {
    global $pdo;
    
    // Vérifier si l'IP est bloquée
    $ip = $_SERVER['REMOTE_ADDR'];
    if (isIPBlocked($ip)) {
        return false; // IP bloquée, refuser la connexion
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['mot_de_passe'])) {
            // Connexion réussie, enregistrer les informations dans la session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = $user['email'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'];
            $_SESSION['role'] = $user['role'];
            
            return $user;
        } else {
            // Connexion échouée, enregistrer la tentative
            recordFailedLoginAttempt($ip);
            return false;
        }
    } catch (PDOException $e) {
        // Gérer l'erreur
        return false;
    }
}

/**
 * Enregistre un nouvel utilisateur
 * @param array $userData
 * @return bool
 */
function registerUser($userData) {
    global $pdo;
    
    try {
        $hashed_password = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, 'utilisateur')");
        $stmt->execute([$userData['nom'], $userData['prenom'], $userData['email'], $hashed_password]);
        
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Déconnecte l'utilisateur
 */
function logoutUser() {
    // Détruire toutes les variables de session
    $_SESSION = array();
    
    // Détruire la session
    session_destroy();
}

/**
 * Récupère les informations d'un utilisateur
 * @param int $userId
 * @return array|bool
 */
function getUserById($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Met à jour les informations d'un utilisateur
 * @param int $userId
 * @param array $userData
 * @return bool
 */
function updateUser($userId, $userData) {
    global $pdo;
    
    try {
        if (isset($userData['password'])) {
            // Mise à jour avec nouveau mot de passe
            $hashed_password = password_hash($userData['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE utilisateurs SET nom = ?, prenom = ?, email = ?, mot_de_passe = ? WHERE id = ?");
            $stmt->execute([$userData['nom'], $userData['prenom'], $userData['email'], $hashed_password, $userId]);
        } else {
            // Mise à jour sans changer le mot de passe
            $stmt = $pdo->prepare("UPDATE utilisateurs SET nom = ?, prenom = ?, email = ? WHERE id = ?");
            $stmt->execute([$userData['nom'], $userData['prenom'], $userData['email'], $userId]);
        }
        
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Compte le nombre total d'utilisateurs
 * @return int
 */
function countUsers() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM utilisateurs");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    } catch (PDOException $e) {
        return 0;
    }
}
?>