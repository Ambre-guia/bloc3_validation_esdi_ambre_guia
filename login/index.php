<?php
require_once('auth.php');
require_once('token.php');

$errors = [];

// Rediriger si déjà connecté
if (isLoggedIn()) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Erreur de sécurité. Veuillez réessayer.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validation des données
        if (empty($email)) {
            $errors[] = "L'email est obligatoire";
        } elseif (!validateEmail($email)) {
            $errors[] = "Format d'email invalide";
        }
        
        if (empty($password)) {
            $errors[] = "Le mot de passe est obligatoire";
        }
        
        // Si aucune erreur, vérifier les identifiants
        if (empty($errors)) {
            $user = loginUser($email, $password);
            
            if ($user) {
                // Régénérer l'ID de session après connexion
                session_regenerate_id(true);
                // Régénérer le token CSRF
                regenerateCSRFToken();
                // Rediriger vers la page d'accueil
                header("Location: ../index.php");
                exit;
            } else {
                $errors[] = "Email ou mot de passe incorrect";
            }
        }
    }
}

// Générer un nouveau token CSRF pour le formulaire
$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Connexion - Librairie XYZ</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
    <header>
        <h1>Librairie XYZ</h1>
    </header>
    
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar">
            <ul>
                <li><a href="../index.php">Accueil</a></li>
                <li><a href="index.php">Connexion</a></li>
                <li><a href="register.php">Inscription</a></li>
            </ul>
        </nav>
        
        <!-- Page Content -->
        <div id="content">
            <div class="container">
                <h2>Connexion</h2>
                
                <?php if (!empty($errors)): ?>
                    <div class="error-message">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= cleanInput($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <div class="form-group">
                        <label for="email">Email :</label>
                        <input type="email" id="email" name="email" value="<?= cleanInput($email ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Mot de passe :</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <button type="submit">Se connecter</button>
                </form>
                
                <p>Pas encore inscrit ? <a href="register.php">Inscrivez-vous</a></p>
            </div>
        </div>
    </div>
    
