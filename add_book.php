<?php
require('config.php');
require_once('login/token.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login/index.php');
    exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Erreur de sécurité. Veuillez réessayer.";
    } else {
        // Collectez et nettoyez les données du formulaire
        $title = cleanInput($_POST['title'] ?? '');
        $author = cleanInput($_POST['author'] ?? '');
        $description = cleanInput($_POST['description'] ?? '');
        $date_publication = cleanInput($_POST['date_publication'] ?? '');
        $isbn = cleanInput($_POST['isbn'] ?? '');
        $coverPath = cleanInput($_POST['cover'] ?? '');

        // Validation des données
        if (empty($title)) {
            $errors[] = "Le titre du livre est requis.";
        }
        
        if (empty($author)) {
            $errors[] = "L'auteur est requis.";
        }
        
        if (empty($date_publication)) {
            $errors[] = "La date de publication est requise.";
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_publication)) {
            $errors[] = "Format de date invalide. Utilisez le format YYYY-MM-DD.";
        }
        
        if (empty($isbn)) {
            $errors[] = "ISBN est requis.";
        } elseif (!preg_match('/^[0-9-]{10,17}$/', $isbn)) {
            $errors[] = "Format ISBN invalide.";
        }
        
        if (empty($coverPath)) {
            $errors[] = "L'URL de l'image est requise.";
        } elseif (!filter_var($coverPath, FILTER_VALIDATE_URL) && !preg_match('/^[\w\/.-]+$/', $coverPath)) {
            $errors[] = "Format d'URL d'image invalide.";
        }

        // Si aucune erreur de validation n'est présente
        if (empty($errors)) {
            try {
                $query = "INSERT INTO livres (titre, auteur, description, date_publication, isbn, photo_url, statut) VALUES (:title, :author, :description, :date_publication, :isbn, :photo_url, 'disponible')"; 
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    ':title' => $title,
                    ':author' => $author,
                    ':description' => $description,
                    ':date_publication' => $date_publication,
                    ':isbn' => $isbn,
                    ':photo_url' => $coverPath
                ]);

                // Régénérer le token CSRF
                regenerateCSRFToken();
                // Indiquer que l'ajout du livre a réussi
                $success = true;
            } catch (PDOException $e) {
                $errors[] = "Erreur lors de l'ajout du livre : " . $e->getMessage();
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
    <title>Ajouter un Livre</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
<header>
        <h1>Ajouter un livre - Librairie XYZ</h1>
    </header>

    <?php if ($success) : ?>
        <p>Le livre a été ajouté avec succès.</p>
        <button onclick="window.location.href = 'books.php'">Retour à la gestion des livres </button>
    <?php else : ?>
        <?php if (!empty($errors)) : ?>
            <div class="error-message">
                <ul>
                    <?php foreach ($errors as $error) : ?>
                        <li><?= cleanInput($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <label for="cover">URL de l'image :</label>
            <input type="text" name="cover" required>
            <label for="title">Titre :</label>
            <input type="text" name="title" required>
            <br>
            <label for="author">Auteur :</label>
            <input type="text" name="author" required>
            <br>
            <label for="description">Description :</label>
            <textarea name="description" required></textarea>
            <br>
            <label for="date_publication">Date de Publication :</label>
            <input type="date" name="date_publication" required>
            <br>
            <label for="isbn">ISBN :</label>
            <input type="text" name="isbn" required>
            <br>
            <button type="submit">Ajouter le livre</button>
        </form>
        <button onclick="window.location.href = 'books.php'">Annuler</button>
    <?php endif; ?>
</body>
</html>
