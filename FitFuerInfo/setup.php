<?php
// setup.php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Check if any users exist
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$user_count = $stmt->fetchColumn();

if ($user_count > 0) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Bitte Benutzername und Passwort ausfüllen.";
    } elseif (!validatePassword($password)) {
        $error = getPasswordErrors($password);
    } else {
        $hash = hashPassword($password);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'Systemverwalter')");
            if ($stmt->execute([$username, $hash])) {
                $success = "Systemverwalter erfolgreich angelegt! Sie können sich nun anmelden.";
            }
        } catch (Exception $e) {
            $error = "Fehler beim Anlegen des Benutzers.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - FitFuerInfo</title>
    <link rel="icon" type="image/jpeg" href="assets/favicon.jpg">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="login-container card">
        <h2 style="text-align:center; color: var(--primary-color); margin-bottom: 1.5rem;">Ersteinrichtung</h2>
        <p style="margin-bottom: 1rem; text-align: center;">Es existieren noch keine Benutzer. Bitte legen Sie den ersten <strong>Systemverwalter</strong> an.</p>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <a href="login.php" class="btn" style="display: block; text-align: center;">Zum Login</a>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Benutzername</label>
                    <input type="text" id="username" name="username" class="form-control" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Passwort</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    <small style="color: var(--text-muted);">Min. 8 Zeichen, Groß-/Kleinbuchstabe, Zahl, Sonderzeichen.</small>
                </div>
                <button type="submit" class="btn" style="width: 100%;">Systemverwalter anlegen</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
