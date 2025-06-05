<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once('../config.php');

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
function loginUser($email, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['mot_de_passe'])) {
            // Connexion réussie, enregistrer les informations dans la session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = $user['email'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'];
            $_SESSION['role'] = $user['role'];
            
            return $user;
        }
    } catch (PDOException $e) {
        // Gérer l'erreur
    }
    
    return false;
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