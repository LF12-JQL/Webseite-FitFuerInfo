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
    <script>
        // Verhindert FOUC (Flackern), indem der Dark-Mode direkt angewendet wird
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark-mode'); // Fallback
        }
    </script>
</head>
<body onload="initTheme()">
    <script>
        function initTheme() {
            if (localStorage.getItem('theme') === 'dark') {
                document.body.classList.add('dark-mode');
            }
        }

        function toggleDarkMode() {
            var isDark = document.body.classList.toggle('dark-mode');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        }
    </script>
    <header>
        <a href="index.php" class="logo">FitFuerInfo</a>
        <nav>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="profile.php">Profil</a></li>
                <li><a href="courses.php">Kurse</a></li>
                <li><a href="rooms.php">Räume & Buchung</a></li>
                <?php if (isSystemverwalter()): 
                    global $pdo;
                    $req_count = $pdo->query("SELECT COUNT(*) FROM users WHERE password_change_status = 'requested'")->fetchColumn();
                ?>
                <li><a href="admin.php">Verwaltung</a></li>
                <li><a href="approvals.php">Genehmigungen <?php if ($req_count > 0): ?><span class="badge"><?= $req_count ?></span><?php endif; ?></a></li>
                <?php endif; ?>
                <li><button onclick="toggleDarkMode()" style="background: none; border: none; cursor: pointer; color: var(--text-main); font-weight: 500;">🌓 Design</button></li>
                <li><a href="logout.php" class="btn btn-secondary">Abmelden (<?= htmlspecialchars($_SESSION['username']) ?>)</a></li>
            </ul>
        </nav>
    </header>
    <div class="container">
