<?php
// profile.php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Aktuellen Status abrufen
$stmt = $pdo->prepare("SELECT username, role, password_change_status FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'request_password_change') {
        if ($user['password_change_status'] === 'none') {
            try {
                $stmt = $pdo->prepare("UPDATE users SET password_change_status = 'requested' WHERE id = ?");
                $stmt->execute([$user_id]);
                $success = "Passwortänderung wurde beantragt. Ein Systemverwalter muss diesen Antrag prüfen.";
                $user['password_change_status'] = 'requested'; // Update für Ansicht
            } catch (Exception $e) {
                $error = "Fehler beim Beantragen der Passwortänderung.";
            }
        }
    } elseif ($_POST['action'] === 'change_password') {
        if ($user['password_change_status'] === 'approved') {
            $password = $_POST['new_password'];
            $password_confirm = $_POST['new_password_confirm'];

            if (empty($password) || empty($password_confirm)) {
                $error = "Bitte beide Passwortfelder ausfüllen.";
            } elseif ($password !== $password_confirm) {
                $error = "Die Passwörter stimmen nicht überein.";
            } elseif (!validatePassword($password)) {
                $error = getPasswordErrors($password);
            } else {
                $hash = hashPassword($password);
                try {
                    $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, password_change_status = 'none' WHERE id = ?");
                    $stmt->execute([$hash, $user_id]);
                    $success = "Passwort erfolgreich geändert.";
                    $user['password_change_status'] = 'none'; // Update für Ansicht
                } catch (Exception $e) {
                    $error = "Fehler beim Ändern des Passworts.";
                }
            }
        } else {
            $error = "Sie haben keine Genehmigung zur Passwortänderung.";
        }
    }
}
?>

<div class="card">
    <h2>Mein Profil</h2>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <p><strong>Benutzername:</strong> <?= htmlspecialchars($user['username']) ?></p>
    <p><strong>Rolle:</strong> <?= htmlspecialchars($user['role']) ?></p>

    <h3 style="margin-top: 2rem;">Sicherheit</h3>
    
    <?php if ($user['password_change_status'] === 'none'): ?>
        <p>Möchten Sie Ihr Passwort ändern? Hierfür benötigen Sie eine Genehmigung.</p>
        <form method="POST" action="">
            <input type="hidden" name="action" value="request_password_change">
            <button type="submit" class="btn btn-secondary">Passwortänderung beantragen</button>
        </form>
    <?php elseif ($user['password_change_status'] === 'requested'): ?>
        <div class="alert" style="background-color: #e2e3e5; color: #383d41; border: 1px solid #d6d8db;">
            Ihr Antrag auf Passwortänderung wird derzeit von einem Systemverwalter geprüft.
        </div>
    <?php elseif ($user['password_change_status'] === 'approved'): ?>
        <div class="alert alert-success">
            Ihr Antrag wurde genehmigt! Sie können jetzt Ihr Passwort ändern.
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="change_password">
            <div class="form-group">
                <label for="new_password">Neues Passwort</label>
                <input type="password" id="new_password" name="new_password" class="form-control" required>
                <small style="color: var(--text-muted);">Min. 8 Zeichen, Groß-/Kleinbuchstabe, Zahl, Sonderzeichen.</small>
            </div>
            <div class="form-group">
                <label for="new_password_confirm">Neues Passwort bestätigen</label>
                <input type="password" id="new_password_confirm" name="new_password_confirm" class="form-control" required>
            </div>
            <button type="submit" class="btn">Passwort speichern</button>
        </form>
    <?php endif; ?>
</div>

</div>
</body>
</html>
