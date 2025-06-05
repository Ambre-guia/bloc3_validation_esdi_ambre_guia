<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Génère un token CSRF et le stocke en session
 * @return string Le token généré
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie si le token CSRF est valide
 * @param string $token Le token à vérifier
 * @return bool True si le token est valide, false sinon
 */
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

/**
 * Régénère le token CSRF après utilisation
 */
function regenerateCSRFToken() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}