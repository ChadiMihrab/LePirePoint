<?php
// 1. Toujours démarrer la session en premier
session_start();

// 2. Inclure la connexion à la base de données (crée la variable $pdo)
require_once 'database.php';

// 3. Vérification de sécurité : l'utilisateur doit être connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

// 4. Initialisation des variables pour la modification
$message = "";
$id_annonce = $_GET['id'] ?? null;
$id_utilisateur = $_SESSION['user_id'];

// Vérification si une annonce est bien ciblée
if (!$id_annonce) {
    die("Erreur : Annonce introuvable.");
}

// 1. SI LE FORMULAIRE EST SOUMIS (Mise à jour)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prix = $_POST['prix'];
    $description = $_POST['description'];

    // On met à jour l'annonce, toujours en vérifiant que c'est le bon propriétaire
    $sql_update = "UPDATE annonces SET nom = :nom, prix = :prix, description = :description 
                   WHERE id = :id_annonce AND id_utilisateur = :id_user";
    $stmt_update = $pdo->prepare($sql_update);
    
    if ($stmt_update->execute([
        ':nom' => $nom,
        ':prix' => $prix,
        ':description' => $description,
        ':id_annonce' => $id_annonce,
        ':id_user' => $id_utilisateur
    ])) {
        $message = "L'annonce a été modifiée avec succès !";
    }
}

// 2. RÉCUPÉRATION DES DONNÉES ACTUELLES (Pour pré-remplir le formulaire)
$sql = "SELECT * FROM annonces WHERE id = :id_annonce AND id_utilisateur = :id_user";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':id_annonce' => $id_annonce,
    ':id_user' => $id_utilisateur
]);
$annonce = $stmt->fetch(PDO::FETCH_ASSOC);

// Si l'annonce n'existe pas ou n'appartient pas à l'utilisateur
if (!$annonce) {
    die("Vous n'avez pas le droit de modifier cette annonce.");
}
include 'header.php'; 

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier une annonce</title>
</head>
<body>
    <h1>Modifier mon annonce</h1>

    <?php if ($message): ?>
        <p style="color: green;"><strong><?= $message ?></strong></p>
    <?php endif; ?>

    <form action="modifier_annonce.php?id=<?= $annonce['id'] ?>" method="POST">
        <p>
            <label>Nom de l'annonce :</label><br>
            <input type="text" name="nom" value="<?= htmlspecialchars($annonce['nom']) ?>" required>
        </p>
        <p>
            <label>Prix (€) :</label><br>
            <input type="number" name="prix" step="0.01" value="<?= htmlspecialchars($annonce['prix']) ?>" required>
        </p>
        <p>
            <label>Description :</label><br>
            <textarea name="description" rows="5" required><?= htmlspecialchars($annonce['description']) ?></textarea>
        </p>
        
        <button type="submit">Enregistrer les modifications</button>
        <a href="mes_annonces.php"><button type="button">Annuler</button></a>
    </form>
</body>
</html>
