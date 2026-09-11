<?php
// includes/auth.php
session_start();

// ========== Sicherheitskonstanten ==========
// Bcrypt-Cost-Faktor (höher = sicherer, aber langsamer)
define('PASSWORD_HASH_COST', 12);

// Rate-Limiting-Einstellungen
define('MAX_LOGIN_ATTEMPTS', 5);          // Max. Fehlversuche
define('LOGIN_LOCKOUT_DURATION', 900);    // Sperrzeit in Sekunden (15 Minuten)
define('LOGIN_ATTEMPTS_DIR', __DIR__ . '/../login_attempts');

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

/**
 * Validiert ein Passwort nach gehärteten Sicherheitsrichtlinien.
 *
 * Anforderungen:
 * - Mindestens 8 Zeichen
 * - Mindestens ein Großbuchstabe (A-Z)
 * - Mindestens ein Kleinbuchstabe (a-z)
 * - Mindestens eine Zahl (0-9)
 * - Mindestens ein Sonderzeichen (!@#$%^&*()_+-= etc.)
 *
 * @param string $password Das zu validierende Passwort
 * @return array ['valid' => bool, 'errors' => string[]]
 */
function validatePassword($password) {
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = 'mindestens 8 Zeichen';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'mindestens einen Kleinbuchstaben';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'mindestens einen Großbuchstaben';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'mindestens eine Zahl';
    }
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        $errors[] = 'mindestens ein Sonderzeichen';
    }

    return empty($errors);
}

/**
 * Gibt eine detaillierte Fehlermeldung für die Passwortvalidierung zurück.
 *
 * @param string $password Das zu prüfende Passwort
 * @return string Die Fehlermeldung oder ein leerer String
 */
function getPasswordErrors($password) {
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = 'mindestens 8 Zeichen';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'einen Kleinbuchstaben';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'einen Großbuchstaben';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'eine Zahl';
    }
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        $errors[] = 'ein Sonderzeichen';
    }

    if (empty($errors)) {
        return '';
    }

    return 'Passwort benötigt: ' . implode(', ', $errors) . '.';
}

/**
 * Erzeugt einen sicheren Passwort-Hash mit erhöhtem Cost-Faktor.
 *
 * @param string $password Das Klartext-Passwort
 * @return string Der bcrypt-Hash
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_HASH_COST]);
}

// ========== Brute-Force-Schutz (Rate-Limiting) ==========

/**
 * Prüft, ob eine IP-Adresse wegen zu vieler Fehlversuche gesperrt ist.
 *
 * @param string $ip Die IP-Adresse
 * @return bool true wenn gesperrt
 */
function isLoginLocked($ip) {
    $file = _getAttemptsFile($ip);
    if (!file_exists($file)) {
        return false;
    }

    $data = json_decode(file_get_contents($file), true);
    if (!$data) {
        return false;
    }

    // Alte Einträge entfernen (älter als Sperrzeit)
    $cutoff = time() - LOGIN_LOCKOUT_DURATION;
    $attempts = isset($data['attempts']) ? $data['attempts'] : [];
    $recent = array_filter($attempts, function ($ts) use ($cutoff) {
        return $ts > $cutoff;
    });

    return count($recent) >= MAX_LOGIN_ATTEMPTS;
}

/**
 * Gibt die verbleibende Sperrzeit in Sekunden zurück.
 *
 * @param string $ip Die IP-Adresse
 * @return int Verbleibende Sekunden
 */
function getLockoutRemaining($ip) {
    $file = _getAttemptsFile($ip);
    if (!file_exists($file)) {
        return 0;
    }

    $data = json_decode(file_get_contents($file), true);
    if (!$data || empty($data['attempts'])) {
        return 0;
    }

    $cutoff = time() - LOGIN_LOCKOUT_DURATION;
    $attempts = isset($data['attempts']) ? $data['attempts'] : [];
    $recent = array_filter($attempts, function ($ts) use ($cutoff) {
        return $ts > $cutoff;
    });

    if (count($recent) < MAX_LOGIN_ATTEMPTS) {
        return 0;
    }

    $oldest = min($recent);
    return ($oldest + LOGIN_LOCKOUT_DURATION) - time();
}

/**
 * Registriert einen fehlgeschlagenen Login-Versuch.
 *
 * @param string $ip Die IP-Adresse
 */
function recordFailedLogin($ip) {
    $dir = LOGIN_ATTEMPTS_DIR;
    if (!is_dir($dir)) {
        mkdir($dir, 0700, true);
    }

    $file = _getAttemptsFile($ip);
    $data = ['attempts' => []];

    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true) ?: ['attempts' => []];
    }

    // Alte Einträge entfernen
    $cutoff = time() - LOGIN_LOCKOUT_DURATION;
    $attempts = isset($data['attempts']) ? $data['attempts'] : [];
    $data['attempts'] = array_filter($attempts, function ($ts) use ($cutoff) {
        return $ts > $cutoff;
    });

    $data['attempts'][] = time();
    file_put_contents($file, json_encode($data), LOCK_EX);
}

/**
 * Setzt die fehlgeschlagenen Login-Versuche nach einem erfolgreichen Login zurück.
 *
 * @param string $ip Die IP-Adresse
 */
function clearFailedLogins($ip) {
    $file = _getAttemptsFile($ip);
    if (file_exists($file)) {
        unlink($file);
    }
}

/**
 * Gibt den Dateipfad für die Fehlversuche einer IP zurück.
 *
 * @param string $ip Die IP-Adresse
 * @return string Dateipfad
 */
function _getAttemptsFile($ip) {
    // IP-Adresse hashen, um Dateinamen-Probleme zu vermeiden
    $hash = md5($ip);
    return LOGIN_ATTEMPTS_DIR . '/' . $hash . '.json';
}
?>
