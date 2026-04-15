<?php
// 1. Démarrage de session
session_start();

// 2. Inclusion de la base de données
require_once 'database.php';

// 3. Sécurité : l'utilisateur doit être connecté pour voir cette page
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

// 4. On récupère uniquement les annonces du membre connecté
$sql = "SELECT * FROM annonces WHERE id_utilisateur = :id_user";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id_user' => $_SESSION['user_id']]);
$mes_annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);
include 'header.php'; 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Annonces - LePirePoint</title>
</head>
<body>
    <h1>Gérer mes annonces</h1>
    
    <a href="index.php">Retour à l'accueil</a> | 
    <a href="creer_annonce.php">+ Déposer une nouvelle annonce</a>
    <hr>

    <?php if (isset($_GET['message']) && $_GET['message'] == 'supprime'): ?>
        <p style="color: orange; font-weight: bold;">L'annonce a bien été supprimée.</p>
    <?php endif; ?>

    <div style="display: flex; flex-wrap: wrap; gap: 20px;">
        <?php if (empty($mes_annonces)): ?>
            <p>Vous n'avez pas encore déposé d'annonces.</p>
        <?php else: ?>
            <?php foreach ($mes_annonces as $annonce): ?>
                <div style="border: 1px solid #ccc; padding: 15px; width: 250px; border-radius: 8px;">
                    <h3><?= htmlspecialchars($annonce['nom']) ?></h3>
                    <p style="color: #e67e22; font-weight: bold;"><?= htmlspecialchars($annonce['prix']) ?> €</p>
                    
                    <br>
                    <a href="modifier_annonce.php?id=<?= $annonce['id'] ?>">
                        <button>Modifier</button>
                    </a>

                    <a href="supprimer_annonce.php?id=<?= $annonce['id'] ?>" 
                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette annonce définitivement ?');">
                        <button style="color: red;">Supprimer</button>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>