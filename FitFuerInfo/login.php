<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php");
            exit;
        } else {
            $error = 'Ungültiger Benutzername oder Passwort.';
        }
    } else {
        $error = 'Bitte füllen Sie alle Felder aus.';
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FitFuerInfo</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="login-container card">
        <h2 style="text-align:center; color: var(--primary-color); margin-bottom: 1.5rem;">FitFuerInfo</h2>
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
            </div>
            <button type="submit" class="btn" style="width: 100%;">Anmelden</button>
        </form>
    </div>
</body>
</html>
