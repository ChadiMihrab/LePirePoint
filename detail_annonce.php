<?php
// 1. Démarrage de la session
session_start();

// 2. Inclusion de la connexion (remplace tout ton bloc host, dbname, try/catch)
require_once 'database.php';

// 3. RÉCUPÉRATION DE L'ANNONCE
// On vérifie si l'ID est bien présent dans l'URL (ex: detail_annonce.php?id=5)
if (!isset($_GET['id'])) {
    die("Erreur : Aucune annonce sélectionnée.");
}

$id_annonce = $_GET['id'];

// SQL de base : on cherche l'annonce par son ID
$stmt = $pdo->prepare("SELECT * FROM annonces WHERE id = :id");
$stmt->execute([':id' => $id_annonce]);
$annonce = $stmt->fetch(PDO::FETCH_ASSOC);

// Si l'ID dans l'URL ne correspond à rien en base
if (!$annonce) {
    die("Erreur : Cette annonce n'existe pas ou a été supprimée.");
}

// 2. GESTION DE L'ENVOI DE MESSAGE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_texte'])) {
    if (isset($_SESSION['user_id'])) {
        $contenu = $_POST['message_texte'];
        $id_expediteur = $_SESSION['user_id'];
        $id_destinataire = $annonce['id_utilisateur']; 

        if ($id_expediteur != $id_destinataire) {
            $sql_msg = "INSERT INTO messages (id_expediteur, id_destinataire, id_annonce, contenu) 
                        VALUES (:exp, :dest, :ann, :contenu)";
            $stmt_msg = $pdo->prepare($sql_msg);
            $stmt_msg->execute([
                ':exp' => $id_expediteur,
                ':dest' => $id_destinataire,
                ':ann' => $annonce['id'],
                ':contenu' => $contenu
            ]);
            $msg_succes = "Votre message a été envoyé !";
        } else {
            $msg_erreur = "Vous ne pouvez pas vous envoyer un message à vous-même.";
        }
    } else {
        $msg_erreur = "Vous devez être connecté pour envoyer un message.";
    }
}
include 'header.php'; 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($annonce['nom']) ?></title>
</head>
<body>
    <a href="index.php">Retour aux annonces</a>
    <hr>

    <h1><?= htmlspecialchars($annonce['nom']) ?></h1>
    <h2 style="color: #e67e22;"><?= htmlspecialchars($annonce['prix']) ?> €</h2>
    
    <?php if (!empty($annonce['photo'])): ?>
        <img src="uploads/<?= htmlspecialchars($annonce['photo']) ?>" alt="Photo de l'annonce" style="max-width: 400px;">
    <?php endif; ?>

    <p><?= nl2br(htmlspecialchars($annonce['description'])) ?></p>

    <div style="background-color: #f9f9f9; padding: 15px; margin-top: 20px; border: 1px solid #ccc;">
        <h3>Contacter le vendeur</h3>
        
        <?php if (isset($msg_succes)) echo "<p style='color:green;'>$msg_succes</p>"; ?>
        <?php if (isset($msg_erreur)) echo "<p style='color:red;'>$msg_erreur</p>"; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
            <form action="" method="POST">
                <textarea name="message_texte" rows="4" style="width: 100%;" required placeholder="Votre message..."></textarea>
                <br><br>
                <button type="submit">Envoyer</button>
            </form>
        <?php else: ?>
            <p><a href="connexion.php">Connectez-vous</a> pour contacter ce vendeur.</p>
        <?php endif; ?>
    </div>
</body>
</html>