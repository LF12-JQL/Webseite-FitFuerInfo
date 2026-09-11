<?php
// user_edit.php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';
requireSystemverwalter();

$success = '';
$error = '';

if (!isset($_GET['id'])) {
    die("Benutzer-ID fehlt.");
}
$target_user_id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$target_user_id]);
$target_user = $stmt->fetch();

if (!$target_user) {
    die("Benutzer nicht gefunden.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update') {
        $username = trim($_POST['username']);
        $role = $_POST['role'];
        $password = $_POST['password']; // optional

        if (empty($username)) {
            $error = 'Benutzername darf nicht leer sein.';
        } else {
            try {
                if (!empty($password)) {
                    if (!validatePassword($password)) {
                        $error = getPasswordErrors($password);
                    } else {
                        $hash = hashPassword($password);
                        $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ?, password_hash = ? WHERE id = ?");
                        $stmt->execute([$username, $role, $hash, $target_user_id]);
                        $success = 'Benutzer erfolgreich aktualisiert.';
                    }
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
                    $stmt->execute([$username, $role, $target_user_id]);
                    $success = 'Benutzer erfolgreich aktualisiert.';
                }
                
                if (!$error) {
                    // Reload data
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$target_user_id]);
                    $target_user = $stmt->fetch();
                }
            } catch (Exception $e) {
                $error = 'Fehler beim Speichern (Benutzername evtl. schon vergeben).';
            }
        }
    } elseif ($_POST['action'] === 'delete') {
        if ($target_user_id == $_SESSION['user_id']) {
            $error = 'Sie können sich nicht selbst löschen.';
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$target_user_id])) {
                header("Location: admin.php");
                exit;
            }
        }
    }
}
?>
<div class="card">
    <h2>Benutzer bearbeiten: <?= htmlspecialchars($target_user['username']) ?></h2>
    <a href="admin.php" class="btn btn-secondary" style="margin-bottom: 1rem;">Zurück</a>
    
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="action" value="update">
        <div class="form-group">
            <label for="username">Benutzername</label>
            <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($target_user['username']) ?>" required>
        </div>
        <div class="form-group">
            <label for="role">Rolle</label>
            <select id="role" name="role" class="form-control">
                <option value="Mitarbeiter" <?= $target_user['role'] === 'Mitarbeiter' ? 'selected' : '' ?>>Mitarbeiter</option>
                <option value="Systemverwalter" <?= $target_user['role'] === 'Systemverwalter' ? 'selected' : '' ?>>Systemverwalter</option>
            </select>
        </div>
        <div class="form-group">
            <label for="password">Neues Passwort (leer lassen, um aktuelles beizubehalten)</label>
            <input type="password" id="password" name="password" class="form-control">
        </div>
        <button type="submit" class="btn">Speichern</button>
    </form>

    <?php if ($target_user_id != $_SESSION['user_id']): ?>
        <div style="margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
            <h3>Gefahrenzone</h3>
            <form method="POST" action="" onsubmit="return confirm('Benutzer wirklich löschen?');">
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="btn" style="background-color: var(--error-color);">Benutzer löschen</button>
            </form>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
