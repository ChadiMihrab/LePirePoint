<?php
// 1. Démarrage de la session
session_start();

// 2. Inclusion de la connexion centralisée
require_once 'database.php';

// 3. Sécurité : Vérifier la connexion et l'ID de l'annonce
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id_utilisateur = $_SESSION['user_id'];
$id_annonce = $_GET['id'];

// On vérifie si le favori existe déjà
$check = $pdo->prepare("SELECT * FROM favoris WHERE id_utilisateur = :user AND id_annonce = :annonce");
$check->execute([':user' => $id_utilisateur, ':annonce' => $id_annonce]);

if ($check->fetch()) {
    // Il existe -> On le retire 
    $delete = $pdo->prepare("DELETE FROM favoris WHERE id_utilisateur = :user AND id_annonce = :annonce");
    $delete->execute([':user' => $id_utilisateur, ':annonce' => $id_annonce]);
} else {
    // Il n'existe pas -> On l'ajoute 
    $insert = $pdo->prepare("INSERT INTO favoris (id_utilisateur, id_annonce) VALUES (:user, :annonce)");
    $insert->execute([':user' => $id_utilisateur, ':annonce' => $id_annonce]);
}

// Retour à la page précédente
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit();
?>