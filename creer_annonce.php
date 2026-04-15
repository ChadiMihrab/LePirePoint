<?php
// 1. On démarre la session en premier (obligatoire pour $_SESSION)
session_start();

// 2. On inclut la connexion à la base de données
// Cela remplace tout ton ancien bloc "Connexion à la base de données"
require_once 'database.php';

// 3. Vérification de sécurité
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

// 4. Initialisation des variables
$message = "";

// Traitement du formulaire quand il est envoyé (Méthode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Récupération des données du formulaire 
    $nom = $_POST['nom'] ?? '';
    $prix = $_POST['prix'] ?? 0;
    $description = $_POST['description'] ?? '';
    $id_categorie = $_POST['id_categorie'] ?? null;
    $id_utilisateur = $_SESSION['user_id']; // L'ID du créateur

    // 2. Gestion de la photo (Simplifiée pour le projet) 
    // Ici, on récupère juste le nom du fichier pour la base de données
    $photo = $_FILES['photo']['name'] ?? 'default.jpg';
    
    // (Optionnel) Déplacer le fichier téléchargé vers un dossier "uploads"
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        move_uploaded_file($_FILES['photo']['tmp_name'], 'uploads/' . $photo);
    }

    // 3. Insertion dans la base de données
    if (!empty($nom) && !empty($prix) && !empty($description)) {
        try {
            $sql = "INSERT INTO annonces (nom, prix, description, photo, id_utilisateur, id_categorie) 
                    VALUES (:nom, :prix, :description, :photo, :id_user, :id_cat)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nom' => $nom,
                ':prix' => $prix,
                ':description' => $description,
                ':photo' => $photo,
                ':id_user' => $id_utilisateur,
                ':id_cat' => $id_categorie
            ]);
            $message = "L'annonce a été publiée avec succès !";
        } catch (PDOException $e) {
            $message = "Erreur lors de l'ajout : " . $e->getMessage();
        }
    } else {
        $message = "Veuillez remplir tous les champs obligatoires.";
    }
}
include 'header.php'; 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Déposer une annonce - LePirePoint</title>
</head>
<body>
    <h1>Déposer une annonce</h1>
    
    <?php if ($message): ?>
        <p><strong><?php echo $message; ?></strong></p>
    <?php endif; ?>

    <form action="creer_annonce.php" method="POST" enctype="multipart/form-data">
        <p>
            <label>Nom de l'annonce :</label><br>
            <input type="text" name="nom" required>
        </p>
        <p>
            <label>Prix (€) :</label><br>
            <input type="number" name="prix" step="0.01" required>
        </p>
        <p>
            <label>Description :</label><br>
            <textarea name="description" rows="5" required></textarea>
        </p>
        <p>
            <label>Photo :</label><br>
            <input type="file" name="photo" required>
        </p>
        <p>
            <label>Catégorie (Optionnel) :</label><br>
            <select name="id_categorie">
                <option value="">-- Choisir --</option>
                <option value="1">Immobilier</option>
                <option value="2">Véhicules</option>
                <option value="3">Loisirs</option>
            </select>
        </p>
        <button type="submit">Publier l'annonce</button>
    </form>
</body>
</html>