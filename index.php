<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ .  '/config.php');
require_once(__DIR__ . '/login/auth.php');

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Rediriger vers books.php si l'utilisateur n'est pas admin
if ($page === 'home' && !isAdmin()) {
    header('Location: books.php');
    exit;
}

if ($page === 'books') {
    include("books.php");
} else {
    include("home.php");
}

include("footer.php");
?>
