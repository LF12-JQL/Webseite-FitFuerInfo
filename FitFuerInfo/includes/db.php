<?php
// includes/db.php
$host = '127.0.0.1';
$db   = 'fitfuerinfo';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    if ($e->getCode() == 1049) {
        // Datenbank existiert nicht -> Auto-Setup
        try {
            $dsn_no_db = "mysql:host=$host;charset=$charset";
            $pdo_temp = new PDO($dsn_no_db, $user, $pass, $options);
            
            $sql_file = __DIR__ . '/../database.sql';
            if (file_exists($sql_file)) {
                $sql = file_get_contents($sql_file);
                $pdo_temp->exec($sql);
                
                // Erneut zur neuen DB verbinden
                $pdo = new PDO($dsn, $user, $pass, $options);
            } else {
                die("Fehler: Datenbank existiert nicht und 'database.sql' wurde nicht gefunden.");
            }
        } catch (\PDOException $ex) {
            die("Fehler beim automatischen Anlegen der Datenbank: " . $ex->getMessage());
        }
    } else {
        die("Datenbankfehler: " . $e->getMessage());
    }
}
?>
