<?php
// approvals.php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';
requireSystemverwalter();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'approve_password') {
            $req_user_id = (int)$_POST['user_id'];
            try {
                $stmt = $pdo->prepare("UPDATE users SET password_change_status = 'approved' WHERE id = ?");
                $stmt->execute([$req_user_id]);
                $success = "Antrag auf Passwortänderung genehmigt.";
            } catch (Exception $e) {
                $error = "Fehler beim Genehmigen des Antrags.";
            }
        } elseif ($_POST['action'] === 'reject_password') {
            $req_user_id = (int)$_POST['user_id'];
            try {
                $stmt = $pdo->prepare("UPDATE users SET password_change_status = 'none' WHERE id = ?");
                $stmt->execute([$req_user_id]);
                $success = "Antrag auf Passwortänderung abgelehnt.";
            } catch (Exception $e) {
                $error = "Fehler beim Ablehnen des Antrags.";
            }
        }
    }
}

$password_requests = $pdo->query("SELECT id, username FROM users WHERE password_change_status = 'requested'")->fetchAll();
?>

<div class="card">
    <h2>Genehmigungen</h2>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div style="margin-top: 1rem;">
        <?php if (count($password_requests) > 0): ?>
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Aktion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($password_requests as $req): ?>
                        <tr>
                            <td><?= htmlspecialchars($req['username']) ?> möchte sein Passwort ändern.</td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <form method="POST" action="" style="margin: 0;">
                                        <input type="hidden" name="action" value="approve_password">
                                        <input type="hidden" name="user_id" value="<?= $req['id'] ?>">
                                        <button type="submit" class="btn" style="background-color: #28a745;">Genehmigen</button>
                                    </form>
                                    <form method="POST" action="" style="margin: 0;">
                                        <input type="hidden" name="action" value="reject_password">
                                        <input type="hidden" name="user_id" value="<?= $req['id'] ?>">
                                        <button type="submit" class="btn btn-secondary" style="background-color: var(--error-color); color: white; border: none;">Ablehnen</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: var(--text-muted);">Aktuell liegen keine Anträge vor.</p>
        <?php endif; ?>
    </div>
</div>

</div>
</body>
</html>
