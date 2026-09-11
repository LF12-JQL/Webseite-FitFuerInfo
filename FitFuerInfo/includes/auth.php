<?php
// includes/auth.php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isSystemverwalter() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Systemverwalter';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireSystemverwalter() {
    requireLogin();
    if (!isSystemverwalter()) {
        die("Zugriff verweigert. Nur für Systemverwalter.");
    }
}

function validatePassword($password) {
    // mindestens vier Zeichen, darunter mindestens ein Kleinbuchstabe und eine Zahl
    if (strlen($password) < 4) {
        return false;
    }
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    return true;
}
?>
