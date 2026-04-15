<?php
session_start();
require_once 'database.php';

$message_erreur = "";
$message_succes = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $mdp = $_POST['mot_de_passe'] ?? '';
    $pseudo = $_POST['pseudo'] ?? '';

    if (!empty($email) && !empty($mdp) && !empty($pseudo)) {
        
        $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $check->execute([$email]);
        
        if ($check->fetch()) {
            $message_erreur = "Cet email est déjà utilisé.";
        } 
        // --- NOUVELLE LOGIQUE DE SÉCURITÉ ---
        elseif (strlen($mdp) < 10) {
            $message_erreur = "Le mot de passe doit faire 10 caractères minimum.";
        } 
        elseif (!preg_match('/[0-9]/', $mdp)) {
            $message_erreur = "Le mot de passe doit contenir au moins un chiffre.";
        } 
        // ------------------------------------
        else {
            $hash = password_hash($mdp, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO utilisateurs (email, mot_de_passe, pseudo) VALUES (?, ?, ?)");
            
            try {
                $ins->execute([$email, $hash, $pseudo]);
                $message_succes = "Compte créé avec succès !";
            } catch (PDOException $e) {
                $message_erreur = "Erreur : " . $e->getMessage();
            }
        }
    } else {
        $message_erreur = "Veuillez remplir les champs obligatoires.";
    }
}

include 'header.php'; 
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow border-warning">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Inscription sécurisée</h2>

                <?php if ($message_erreur): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($message_erreur) ?></div>
                <?php endif; ?>
                
                <?php if ($message_succes): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($message_succes) ?></div>
                <?php endif; ?>

                <form action="inscription.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Pseudo :</label>
                        <input type="text" name="pseudo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email :</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mot de passe :</label>
                        <input type="password" name="mot_de_passe" class="form-control" required>
                        <ul class="form-text mt-2">
                            <li>Minimum 10 caractères</li>
                            <li>Au moins un chiffre (0-9)</li>
                        </ul>
                    </div>
                    <button type="submit" class="btn btn-warning w-100 fw-bold">Créer mon compte</button>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>