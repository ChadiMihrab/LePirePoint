<?php
// database.php
$host = 'localhost';
$dbname = 'leboncoin_db';
$user = 'root';
$pass = 'root'; // Mettez 'root' ou '' selon votre test précédent

try {
    // On crée l'objet $pdo qui sera utilisé dans tous les autres fichiers
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    
    // On active les erreurs SQL pour débugger plus facilement
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // Si la connexion échoue, on arrête tout et on affiche l'erreur
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>