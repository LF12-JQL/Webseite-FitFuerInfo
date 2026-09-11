<?php
require_once 'includes/db.php';

$admin_hash = password_hash('admin123', PASSWORD_DEFAULT);
$user_hash = password_hash('user123', PASSWORD_DEFAULT);

$pdo->exec("UPDATE users SET password_hash = '$admin_hash' WHERE username = 'admin'");
$pdo->exec("UPDATE users SET password_hash = '$user_hash' WHERE username = 'mitarbeiter1'");

echo "Passwörter wurden erfolgreich korrigiert! Sie können diese Seite nun schließen und sich auf der Login-Seite mit admin / admin123 anmelden.";
?>
