<?php
session_start();
require_once 'database.php';

// 1. Sécurité : on vérifie que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

$mon_id = $_SESSION['user_id'];
$message = "";
$erreur = "";

// 2. On récupère les informations actuelles pour pré-remplir le formulaire
$stmt = $pdo->prepare("SELECT email, pseudo FROM utilisateurs WHERE id = ?");
$stmt->execute([$mon_id]);
$user = $stmt->fetch();

// 3. Traitement de la mise à jour (quand on clique sur Enregistrer)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouveau_pseudo = $_POST['pseudo'] ?? '';
    $nouvel_email = $_POST['email'] ?? '';
    $nouveau_mdp = $_POST['nouveau_mdp'] ?? '';

    // A. Vérification de l'email (ne doit pas être déjà pris par quelqu'un d'autre)
    $checkEmail = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
    $checkEmail->execute([$nouvel_email, $mon_id]);
    
    if ($checkEmail->fetch()) {
        $erreur = "Cet email est déjà utilisé par un autre compte.";
    } else {
        try {
            // B. Préparation de la requête SQL de base
            $sql = "UPDATE utilisateurs SET pseudo = :ps, email = :em";
            $params = [':ps' => $nouveau_pseudo, ':em' => $nouvel_email, ':id' => $mon_id];

            // C. Si un nouveau mot de passe est saisi, on l'ajoute à la requête
            if (!empty($nouveau_mdp)) {
                if (strlen($nouveau_mdp) < 10) {
                    $message_erreur = "Le nouveau mot de passe doit faire 10 caractères minimum.";
                    $erreur_trouvee = true;
                } elseif (!preg_match('/[0-9]/', $nouveau_mdp)) {
                    $message_erreur = "Le nouveau mot de passe doit contenir au moins un chiffre.";
                    $erreur_trouvee = true;
                }
            }

            $sql .= " WHERE id = :id";
            $update = $pdo->prepare($sql);
            $update->execute($params);

            $message = "Profil mis à jour avec succès !";
            // On met à jour les infos pour l'affichage
            $user['pseudo'] = $nouveau_pseudo;
            $user['email'] = $nouvel_email;
            
        } catch (Exception $e) {
            $erreur = $e->getMessage();
        }
    }
}

include 'header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Modifier mon profil</h2>

                <?php if ($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>

                <?php if ($erreur): ?>
                    <div class="alert alert-danger"><?= $erreur ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Pseudo :</label>
                        <input type="text" name="pseudo" class="form-control" value="<?= htmlspecialchars($user['pseudo']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email :</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Nouveau mot de passe :</label>
                        <input type="password" name="nouveau_mdp" class="form-control" placeholder="Laissez vide pour ne pas changer">
                        <small class="text-muted">Minimum 10 caractères si vous le modifiez.</small>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Enregistrer les modifications</button>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>