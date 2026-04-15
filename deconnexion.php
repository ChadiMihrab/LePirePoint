<?php
// 1. On récupère la session en cours
session_start();

// 2. On détruit toutes les données de la session (l'utilisateur est déconnecté)
session_destroy();

// 3. On le renvoie vers la page d'accueil
header("Location: index.php");
exit();
?>