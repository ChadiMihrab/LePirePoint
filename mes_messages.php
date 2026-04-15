<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

$mon_id = $_SESSION['user_id'];

// TRAITEMENT : ENVOI D'UN MESSAGE DEPUIS LE CHAT 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nouveau_message'])) {
    $msg = $_POST['contenu'];
    $dest = $_POST['id_destinataire'];
    $ann = $_POST['id_annonce'];
    
    if (!empty($msg)) {
        $ins = $pdo->prepare("INSERT INTO messages (id_expediteur, id_destinataire, id_annonce, contenu) VALUES (?, ?, ?, ?)");
        $ins->execute([$mon_id, $dest, $ann, $msg]);
        // On recharge la page pour voir le message
        header("Location: mes_messages.php?contact=$dest&annonce=$ann");
        exit();
    }
}

include 'header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        
        <?php
        // ==========================================
        // ÉCRAN 2 : LE CHAT OUVERT (Si on a cliqué sur une conversation)
        // ==========================================
        if (isset($_GET['contact']) && isset($_GET['annonce'])): 
            $contact_id = $_GET['contact'];
            $annonce_id = $_GET['annonce'];

            // A. Marquer les messages reçus de ce contact comme "lus" (efface la notification)
            $updateLu = $pdo->prepare("UPDATE messages SET lu = 1 WHERE id_destinataire = ? AND id_expediteur = ? AND id_annonce = ?");
            $updateLu->execute([$mon_id, $contact_id, $annonce_id]);

            // B. Récupérer le pseudo du contact
            $stmtContact = $pdo->prepare("SELECT pseudo FROM utilisateurs WHERE id = ?");
            $stmtContact->execute([$contact_id]);
            $contact_pseudo = $stmtContact->fetchColumn();

            // C. Récupérer tout l'historique de cette discussion (classé du plus vieux au plus récent)
            $sqlChat = "SELECT * FROM messages 
                        WHERE id_annonce = ? 
                        AND ((id_expediteur = ? AND id_destinataire = ?) OR (id_expediteur = ? AND id_destinataire = ?))
                        ORDER BY date_envoi ASC";
            $stmtChat = $pdo->prepare($sqlChat);
            $stmtChat->execute([$annonce_id, $mon_id, $contact_id, $contact_id, $mon_id]);
            $historique = $stmtChat->fetchAll();
        ?>

            <div class="card shadow">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Chat avec <?= htmlspecialchars($contact_pseudo) ?></h5>
                    <a href="mes_messages.php" class="btn btn-sm btn-outline-light">🔙 Retour</a>
                </div>
                
                <div class="card-body" style="height: 400px; overflow-y: scroll; background-color: #f8f9fa;">
                    <?php foreach ($historique as $msg): ?>
                        <?php $c_est_moi = ($msg['id_expediteur'] == $mon_id); ?>
                        
                        <div class="d-flex mb-3 <?= $c_est_moi ? 'justify-content-end' : 'justify-content-start' ?>">
                            <div class="p-3 rounded shadow-sm <?= $c_est_moi ? 'bg-primary text-white' : 'bg-white border' ?>" style="max-width: 75%;">
                                <?= nl2br(htmlspecialchars($msg['contenu'])) ?>
                                <div class="text-end mt-1" style="font-size: 0.75rem; <?= $c_est_moi ? 'color: #d1e7dd;' : 'color: #6c757d;' ?>">
                                    <?= date('d/m à H:i', strtotime($msg['date_envoi'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="card-footer bg-white">
                    <form method="POST" class="d-flex">
                        <input type="hidden" name="id_destinataire" value="<?= htmlspecialchars($contact_id) ?>">
                        <input type="hidden" name="id_annonce" value="<?= htmlspecialchars($annonce_id) ?>">
                        <input type="text" name="contenu" class="form-control me-2" placeholder="Écrivez votre message..." required autofocus>
                        <button type="submit" name="nouveau_message" class="btn btn-warning fw-bold">Envoyer</button>
                    </form>
                </div>
            </div>

        <?php
        // ==========================================
        // ÉCRAN 1 : LA BOÎTE DE RÉCEPTION (Liste)
        // ==========================================
        else: 
            // Astuce PHP : On récupère tous nos messages et on les groupe en PHP
            $sqlListe = "SELECT m.*, u.pseudo AS autre_pseudo, a.nom AS nom_annonce 
                         FROM messages m
                         JOIN annonces a ON m.id_annonce = a.id
                         JOIN utilisateurs u ON (u.id = m.id_expediteur OR u.id = m.id_destinataire) AND u.id != :mon_id
                         WHERE m.id_expediteur = :mon_id OR m.id_destinataire = :mon_id
                         ORDER BY m.date_envoi DESC";
            
            $stmtListe = $pdo->prepare($sqlListe);
            $stmtListe->execute([':mon_id' => $mon_id]);
            $tous_les_messages = $stmtListe->fetchAll();

            $conversations = [];
            foreach ($tous_les_messages as $m) {
                // On crée une clé unique par conversation (Contact + Annonce)
                $cle = $m['autre_pseudo'] . "_" . $m['id_annonce'];
                
                if (!isset($conversations[$cle])) {
                    $conversations[$cle] = [
                        'contact_id' => ($m['id_expediteur'] == $mon_id) ? $m['id_destinataire'] : $m['id_expediteur'],
                        'pseudo' => $m['autre_pseudo'],
                        'annonce_id' => $m['id_annonce'],
                        'annonce_nom' => $m['nom_annonce'],
                        'dernier_msg' => $m['contenu'],
                        'date' => $m['date_envoi'],
                        'non_lus' => 0
                    ];
                }
                // Si le message m'est destiné et n'est pas lu, on augmente le compteur de notification
                if ($m['id_destinataire'] == $mon_id && $m['lu'] == 0) {
                    $conversations[$cle]['non_lus']++;
                }
            }
        ?>
            <h2 class="mb-4">✉️ Mes Conversations</h2>
            
            <?php if (empty($conversations)): ?>
                <div class="alert alert-info">Vous n'avez aucun message.</div>
            <?php else: ?>
                <div class="list-group shadow-sm">
                    <?php foreach ($conversations as $conv): ?>
                        <a href="mes_messages.php?contact=<?= $conv['contact_id'] ?>&annonce=<?= $conv['annonce_id'] ?>" 
                           class="list-group-item list-group-item-action p-4 <?= ($conv['non_lus'] > 0) ? 'bg-light' : '' ?>">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-2">
                                <h5 class="mb-1 fw-bold text-dark">
                                    👤 <?= htmlspecialchars($conv['pseudo']) ?> 
                                    <span class="text-muted fw-normal" style="font-size: 0.9rem;">- <?= htmlspecialchars($conv['annonce_nom']) ?></span>
                                </h5>
                                <small class="text-muted"><?= date('d/m/Y', strtotime($conv['date'])) ?></small>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="mb-0 text-truncate text-muted" style="max-width: 80%;">
                                    <?= htmlspecialchars($conv['dernier_msg']) ?>
                                </p>
                                
                                <?php if ($conv['non_lus'] > 0): ?>
                                    <span class="badge bg-danger rounded-pill fs-6"><?= $conv['non_lus'] ?> message(s)</span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php endif; // Fin du bloc if/else des écrans ?>

    </div>
</div>

</div> </body>
</html>