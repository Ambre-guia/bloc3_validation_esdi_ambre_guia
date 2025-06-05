<?php
require_once('auth.php');

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = false;

// Récupérer les informations de l'utilisateur
$user = getUserById($user_id);

if (!$user) {
    header("Location: logout.php");
    exit;
}

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $new_password_confirm = $_POST['new_password_confirm'] ?? '';
    
    // Validation des données
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire";
    }
    
    if (empty($prenom)) {
        $errors[] = "Le prénom est obligatoire";
    }
    
    if (empty($email)) {
        $errors[] = "L'email est obligatoire";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide";
    } elseif ($email !== $user['email']) {
        // Vérifier si le nouvel email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "Cet email est déjà utilisé";
        }
    }
    
    // Vérification du mot de passe actuel si l'utilisateur souhaite le changer
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $errors[] = "Le mot de passe actuel est obligatoire pour changer de mot de passe";
        } elseif (!password_verify($current_password, $user['mot_de_passe'])) {
            $errors[] = "Le mot de passe actuel est incorrect";
        }
        
        if (strlen($new_password) < 6) {
            $errors[] = "Le nouveau mot de passe doit contenir au moins 6 caractères";
        }
        
        if ($new_password !== $new_password_confirm) {
            $errors[] = "Les nouveaux mots de passe ne correspondent pas";
        }
    }
    
    // Si aucune erreur, mettre à jour les informations
    if (empty($errors)) {
        $userData = [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email
        ];
        
        if (!empty($new_password)) {
            $userData['password'] = $new_password;
        }
        
        if (updateUser($user_id, $userData)) {
            // Mettre à jour les informations de session
            $_SESSION['user'] = $email;
            $_SESSION['nom'] = $nom;
            $_SESSION['prenom'] = $prenom;
            
            $success = true;
            
            // Récupérer les informations mises à jour
            $user = getUserById($user_id);
        } else {
            $errors[] = "Erreur lors de la mise à jour. Veuillez réessayer.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mon Profil - Librairie XYZ</title>
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
                <li><a href="../books.php">Voir la liste des livres</a></li>
                <li><a href="profile.php">Mon profil</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li><a href="../admin.php">Administration</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <!-- Page Content -->
        <div id="content">
            <div class="container">
                <h2>Mon Profil</h2>
                
                <?php if ($success): ?>
                    <div class="success-message">
                        <p>Vos informations ont été mises à jour avec succès !</p>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="error-message">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div class="profile-info">
                    <p><strong>Nom :</strong> <?= htmlspecialchars($user['nom']) ?></p>
                    <p><strong>Prénom :</strong> <?= htmlspecialchars($user['prenom']) ?></p>
                    <p><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></p>
                    <p><strong>Date d'inscription :</strong> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($user['date_inscription']))) ?></p>
                    <p><strong>Rôle :</strong> <?= htmlspecialchars($user['role']) ?></p>
                </div>
                
                <h3>Modifier mes informations</h3>
                <form method="post" action="">
                    <div class="form-group">
                        <label for="nom">Nom :</label>
                        <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="prenom">Prénom :</label>
                        <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email :</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    
                    <h4>Changer de mot de passe (optionnel)</h4>
                    
                    <div class="form-group">
                        <label for="current_password">Mot de passe actuel :</label>
                        <input type="password" id="current_password" name="current_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">Nouveau mot de passe :</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password_confirm">Confirmer le nouveau mot de passe :</label>
                        <input type="password" id="new_password_confirm" name="new_password_confirm">
                    </div>
                    
                    <button type="submit">Mettre à jour</button>
                </form>
            </div>
        </div>
    </div>
    
    <?php include("../footer.php"); ?>
</body>
</html>