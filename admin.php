<?php

require_once('config.php');
require_once('login/auth.php');

// Vérifier si l'utilisateur est connecté et est admin
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

// Traitement de la modification du rôle utilisateur si formulaire soumis
if (isset($_POST['update_role'])) {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['role'];
    
    try {
        $stmt = $pdo->prepare("UPDATE utilisateurs SET role = ? WHERE id = ?");
        $stmt->execute([$new_role, $user_id]);
        $success_message = "Rôle mis à jour avec succès.";
    } catch (PDOException $e) {
        $error_message = "Erreur lors de la mise à jour du rôle : " . $e->getMessage();
    }
}

// Récupérer tous les utilisateurs
$query = "SELECT * FROM utilisateurs ORDER BY nom, prenom";
$stmt = $pdo->query($query);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Administration - Librairie XYZ</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <header>
        <h1>Administration - Librairie XYZ</h1>
    </header>

    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar">
            <ul>
                <li>Bonjour <?= $_SESSION['prenom']; ?></li>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="books.php">Voir la liste des livres</a></li>
                <li><a href="login/profile.php">Mon profil</a></li>
                <li><a href="login/logout.php">Deconnexion</a></li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <div class="container">
                <h1>Gestion des Utilisateurs</h1>
                
                <?php if (isset($success_message)): ?>
                    <div class="success-message"><?= $success_message ?></div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="error-message"><?= $error_message ?></div>
                <?php endif; ?>
                
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= $user['nom'] ?></td>
                        <td><?= $user['prenom'] ?></td>
                        <td><?= $user['email'] ?></td>
                        <td><?= $user['role'] ?></td>
                        <td>
                            <form method="post" action="">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <select name="role">
                                    <option value="utilisateur" <?= $user['role'] === 'utilisateur' ? 'selected' : '' ?>>Utilisateur</option>
                                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                                </select>
                                <button type="submit" name="update_role">Modifier</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
</body>
</html>