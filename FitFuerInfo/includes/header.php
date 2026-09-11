<?php
// includes/header.php
require_once 'auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitFuerInfo - Raumbuchung</title>
    <link rel="icon" type="image/jpeg" href="assets/favicon.jpg">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header>
        <a href="index.php" class="logo">FitFuerInfo</a>
        <nav>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="courses.php">Kurse</a></li>
                <li><a href="rooms.php">Räume & Buchung</a></li>
                <?php if (isSystemverwalter()): ?>
                <li><a href="admin.php">Verwaltung</a></li>
                <?php endif; ?>
                <li><a href="logout.php" class="btn btn-secondary">Abmelden (<?= htmlspecialchars($_SESSION['username']) ?>)</a></li>
            </ul>
        </nav>
    </header>
    <div class="container">
