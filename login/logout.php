<?php
require_once('auth.php');

// Déconnecter l'utilisateur
logoutUser();

// Rediriger vers la page d'accueil
header("Location: ../index.php");
exit;
?>