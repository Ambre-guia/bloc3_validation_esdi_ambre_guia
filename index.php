<?php


require_once('config.php');
require_once('login/auth.php');

// Liste des pages autorisées
$allowed_pages = ['home', 'books'];

// Récupérer la page demandée et la valider
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Vérifier que la page demandée est dans la liste des pages autorisées
if (!in_array($page, $allowed_pages)) {
    $page = 'home'; // Page par défaut si non autorisée
}

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
