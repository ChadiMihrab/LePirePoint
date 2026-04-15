<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

$mon_id = $_SESSION['user_id'];

// SQL de base : on pioche dans les deux tables et on les lie avec un "WHERE"
// On dit : "Prends l'annonce SI son ID est égal à l'id_annonce enregistré dans mes favoris"
$sql = "SELECT annonces.* FROM annonces, favoris 
        WHERE favoris.id_annonce = annonces.id 
        AND favoris.id_utilisateur = :user_id";

$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $mon_id]);
$mes_favoris = $stmt->fetchAll(PDO::FETCH_ASSOC);
include 'header.php'; 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Favoris</title>
</head>
<body>
    <h1>Mes annonces favorites</h1>
    <a href="index.php">Retour à l'accueil</a>
    <hr>

    <ul>
        <?php foreach ($mes_favoris as $fav): ?>
            <li>
                <strong><?= htmlspecialchars($fav['nom']) ?></strong> - <?= htmlspecialchars($fav['prix']) ?> € 
                <a href="detail_annonce.php?id=<?= $fav['id'] ?>"> (Voir)</a>
                <a href="action_favori.php?id=<?= $fav['id'] ?>" style="color: red;"> [X] Retirer</a>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php if (empty($mes_favoris)) echo "<p>Vous n'avez aucun favori pour le moment.</p>"; ?>
</body>
</html>