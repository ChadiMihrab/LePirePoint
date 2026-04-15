<?php
session_start();
require_once 'database.php';

$sql = "SELECT * FROM annonces WHERE 1=1"; 
$params = [];

if (!empty($_GET['categorie'])) {
    $sql .= " AND id_categorie = :categorie";
    $params[':categorie'] = $_GET['categorie'];
}

if (!empty($_GET['prix_max'])) {
    $sql .= " AND prix <= :prix_max";
    $params[':prix_max'] = $_GET['prix_max'];
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);

// INCLURE LE HEADER
include 'header.php'; 
?>

<h1 class="mb-4">Toutes les pires annonces</h1>

<form action="index.php" method="GET" class="row g-3 mb-5 p-3 bg-light border rounded">
    <div class="col-md-4">
        <label class="form-label">Catégorie :</label>
        <select name="categorie" class="form-select">
            <option value="">Toutes</option>
            <option value="1">Immobilier</option>
            <option value="2">Véhicules</option>
            <option value="3">Loisirs</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Prix max (€) :</label>
        <input type="number" name="prix_max" class="form-control" placeholder="Ex: 500">
    </div>
    <div class="col-md-4 d-flex align-items-end">
        <button type="submit" class="btn btn-primary me-2">Filtrer</button>
        <a href="index.php" class="btn btn-outline-secondary">Réinitialiser</a>
    </div>
</form>

<div class="row">
    <?php if (empty($annonces)): ?>
        <div class="col-12">
            <p class="alert alert-warning">Aucune annonce ne correspond à votre recherche.</p>
        </div>
    <?php else: ?>
        <?php foreach ($annonces as $annonce): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <?php if (!empty($annonce['photo'])): ?>
                        <img src="uploads/<?= htmlspecialchars($annonce['photo']) ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($annonce['nom']) ?></h5>
                        <p class="text-primary fw-bold"><?= htmlspecialchars($annonce['prix']) ?> €</p>
                        <p class="card-text small text-muted">
                            <?= htmlspecialchars(substr($annonce['description'], 0, 80)) ?>...
                        </p>
                    </div>
                    
                    <div class="card-footer bg-white border-top-0 d-flex justify-content-between">
                        <a href="detail_annonce.php?id=<?= $annonce['id'] ?>" class="btn btn-sm btn-outline-primary">Détails</a>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="action_favori.php?id=<?= $annonce['id'] ?>" class="btn btn-sm btn-outline-warning">⭐ Favori</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</div> </body>
</html>