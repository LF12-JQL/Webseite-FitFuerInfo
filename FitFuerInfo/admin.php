<?php
// admin.php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';
requireSystemverwalter();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_user') {
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            $role = $_POST['role'];
            
            if (empty($username) || empty($password)) {
                $error = "Bitte Benutzername und Passwort ausfüllen.";
            } elseif (!validatePassword($password)) {
                $error = "Passwort muss mindestens 4 Zeichen, einen Kleinbuchstaben und eine Zahl enthalten.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                try {
                    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
                    if ($stmt->execute([$username, $hash, $role])) {
                        $success = "Benutzer erfolgreich angelegt.";
                    }
                } catch (Exception $e) {
                    $error = "Fehler beim Anlegen des Benutzers (eventuell existiert der Name schon).";
                }
            }
        } elseif ($_POST['action'] === 'add_room') {
            $name = trim($_POST['name']);
            $workstations = (int)$_POST['workstations'];
            
            if (!empty($name) && $workstations > 0) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO rooms (name, workstations) VALUES (?, ?)");
                    if ($stmt->execute([$name, $workstations])) {
                        $success = "Raum erfolgreich angelegt.";
                    }
                } catch (Exception $e) {
                    $error = "Fehler beim Anlegen des Raums.";
                }
            } else {
                $error = "Ungültige Raumdaten.";
            }
        }
    }
}

$users = $pdo->query("SELECT id, username, role FROM users ORDER BY username")->fetchAll();
$rooms = $pdo->query("SELECT * FROM rooms ORDER BY name")->fetchAll();
?>

<div class="card">
    <h2>Verwaltungsbereich</h2>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div style="display: flex; gap: 2rem;">
        <div style="flex: 1;">
            <h3>Benutzer anlegen</h3>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_user">
                <div class="form-group">
                    <label for="username">Benutzername</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Passwort (min. 4 Zeichen, 1 Kleinbuchstabe, 1 Zahl)</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="role">Rolle</label>
                    <select id="role" name="role" class="form-control">
                        <option value="Mitarbeiter">Mitarbeiter</option>
                        <option value="Systemverwalter">Systemverwalter</option>
                    </select>
                </div>
                <button type="submit" class="btn">Benutzer anlegen</button>
            </form>
            
            <h3 style="margin-top: 2rem;">Alle Benutzer</h3>
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Rolle</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['username']) ?></td>
                            <td><?= htmlspecialchars($u['role']) ?></td>
                            <td>
                                <a href="user_edit.php?id=<?= $u['id'] ?>" class="btn">Bearbeiten</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div style="flex: 1;">
            <h3>Raum anlegen</h3>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_room">
                <div class="form-group">
                    <label for="name">Raumname</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="workstations">Arbeitsplätze</label>
                    <input type="number" id="workstations" name="workstations" class="form-control" min="1" required>
                </div>
                <button type="submit" class="btn">Raum anlegen</button>
            </form>
            
            <h3 style="margin-top: 2rem;">Alle Räume</h3>
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Plätze</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['name']) ?></td>
                            <td><?= htmlspecialchars($r['workstations']) ?></td>
                            <td>
                                <a href="room_edit.php?id=<?= $r['id'] ?>" class="btn">Bearbeiten</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>
</body>
</html>
