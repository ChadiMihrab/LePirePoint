<?php
// 1. On démarre la session (seulement si elle n'est pas déjà lancée)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Inclusion de la base de données
require_once 'database.php';

$nb_notifs = 0;

// 3. Calcul des notifications si l'utilisateur est connecté
if (isset($_SESSION['user_id'])) {
    $mon_id = $_SESSION['user_id'];
    
    // On compte les discussions (Expéditeur + Annonce) contenant des messages non lus pour moi
    $sqlNotif = "SELECT COUNT(DISTINCT id_expediteur, id_annonce) 
                 FROM messages 
                 WHERE id_destinataire = ? AND lu = 0";
    
    $stmtNotif = $pdo->prepare($sqlNotif);
    $stmtNotif->execute([$mon_id]);
    $nb_notifs = $stmtNotif->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LePirePoint - Le meilleur du pire</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">🛒 LePirePoint</a>
        
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Accueil</a>
                </li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="creer_annonce.php">Déposer une annonce</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="mes_annonces.php">Mes annonces</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="mes_favoris.php">⭐ Mes favoris</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="mes_messages.php">
                            ✉️ Messagerie
                            <?php if ($nb_notifs > 0): ?>
                                <span class="badge rounded-pill bg-danger ms-1"><?= $nb_notifs ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profil.php">👤 Mon Profil</a>
                    </li>
                    <li class="nav-item d-flex align-items-center">
                        <a class="btn btn-sm btn-danger ms-3" href="deconnexion.php">Déconnexion</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item d-flex align-items-center">
                        <a class="btn btn-sm btn-outline-light me-2" href="connexion.php">Se connecter</a>
                    </li>
                    <li class="nav-item d-flex align-items-center">
                        <a class="btn btn-sm btn-warning" href="inscription.php">S'inscrire</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container">