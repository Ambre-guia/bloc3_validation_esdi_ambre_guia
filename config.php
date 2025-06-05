<?php
// Configuration des cookies de session
ini_set('session.cookie_httponly', 1); // Protection aux cookies via JavaScript
ini_set('session.use_only_cookies', 1); // Force l'utilisation des cookies
ini_set('session.cookie_secure', 1); // Cookies uniquement via HTTPS 
ini_set('session.cookie_samesite', 'Strict'); // Protection contre CSRF

// Démarrer ou reprendre la session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Régénérer l'ID de session périodiquement pour éviter la fixation de session
if (!isset($_SESSION['last_regeneration']) || time() - $_SESSION['last_regeneration'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

$host = 'localhost:3306';
$dbname = 'exam3_esdi';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Configurer PDO pour qu'il lance des exceptions en cas d'erreur
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Forcer PDO à retourner uniquement des colonnes associatives
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // Émuler les requêtes préparées pour une meilleure sécurité
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    // Ne pas afficher les détails de l'erreur en production
    die("Erreur de connexion à la base de données. Veuillez contacter l'administrateur.");
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Ajouter après la configuration PDO

/**
 * Nettoie les données d'entrée pour prévenir les attaques XSS
 * @param string|array $data Les données à nettoyer
 * @return string|array Les données nettoyées
 */
function cleanInput($data) {
    if (is_array($data)) {
        $cleaned = [];
        foreach ($data as $key => $value) {
            $cleaned[$key] = cleanInput($value);
        }
        return $cleaned;
    }
    
    // Convertit les caractères spéciaux en entités HTML
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Fonction pour valider un email
 * @param string $email L'email à valider
 * @return bool True si l'email est valide, false sinon
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}