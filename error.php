<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$error_code = isset($_GET['code']) ? intval($_GET['code']) : 404;
$error_messages = [
    404 => "Page non trouvée",
    403 => "Accès interdit",
    500 => "Erreur interne du serveur"
];

$error_message = $error_messages[$error_code] ?? "Erreur inconnue";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Erreur <?= $error_code ?> - Librairie XYZ</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <style>
        .error-container {
            text-align: center;
            padding: 50px 20px;
        }
        .error-code {
            font-size: 72px;
            color: #e74c3c;
            margin-bottom: 20px;
        }
        .error-message {
            font-size: 24px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Librairie XYZ</h1>
    </header>
    
    <div class="error-container">
        <div class="error-code"><?= $error_code ?></div>
        <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
        <p>Désolé pour ce désagrément. Veuillez retourner à <a href="index.php">la page d'accueil</a>.</p>
    </div>
    
    <?php include("footer.php"); ?>
</body>
</html>