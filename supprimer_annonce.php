<?php
session_start();

// 1. Inclusion de la base de données (crée la variable $pdo)
require_once 'database.php';

// 2. Vérification de sécurité : l'utilisateur doit être connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

// 3. Vérifier si un ID d'annonce a bien été envoyé dans l'URL
if (isset($_GET['id'])) {
    $id_annonce = $_GET['id'];
    $id_utilisateur = $_SESSION['user_id'];

    try {
        // Requête de suppression : on vérifie que l'annonce appartient bien à l'utilisateur
        $sql = "DELETE FROM annonces WHERE id = :id_annonce AND id_utilisateur = :id_user";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_annonce' => $id_annonce,
            ':id_user' => $id_utilisateur
        ]);

        // Redirection vers la liste des annonces après suppression
        header('Location: mes_annonces.php?message=supprime');
        exit();

    } catch (PDOException $e) {
        die("Erreur lors de la suppression : " . $e->getMessage());
    }
} else {
    echo "Aucune annonce spécifiée.";
}
?>