<?php
// admin.php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/version.php';
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
                $error = getPasswordErrors($password);
            } else {
                $hash = hashPassword($password);
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
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <h2 style="margin: 0;">Verwaltungsbereich</h2>
        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <span style="color: var(--text-muted); font-size: 0.9rem;">Version: <strong><?= APP_VERSION ?></strong></span>
            <button type="button" id="btn-update-check" class="btn btn-secondary" onclick="checkForUpdates()" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">Auf Updates pr&uuml;fen</button>
        </div>
    </div>
    <div id="update-result" style="display: none; margin-top: 1rem;"></div>
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
                    <label for="password">Passwort (min. 8 Zeichen, Groß-/Kleinbuchstabe, Zahl, Sonderzeichen)</label>
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

<script>
function checkForUpdates() {
    var btn = document.getElementById('btn-update-check');
    var resultDiv = document.getElementById('update-result');

    btn.disabled = true;
    btn.textContent = 'Pr\u00fcfe...';
    resultDiv.style.display = 'block';
    resultDiv.innerHTML = '<div style="color: var(--text-muted); padding: 0.75rem;">Verbindung zu GitHub wird hergestellt...</div>';

    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'check_update.php', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            btn.disabled = false;
            btn.textContent = 'Auf Updates pr\u00fcfen';

            if (xhr.status === 200) {
                try {
                    var data = JSON.parse(xhr.responseText);

                    if (data.error) {
                        resultDiv.innerHTML = '<div class="alert alert-error">' + escapeHtml(data.error) + '</div>';
                        return;
                    }

                    if (data.update_available) {
                        resultDiv.innerHTML = '<div class="alert" style="background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 1rem; border-radius: 0.375rem;">' +
                            '<strong>&#9888; Update verf\u00fcgbar!</strong><br>' +
                            escapeHtml(data.message) + '<br><br>' +
                            '<a href="' + escapeHtml(data.download_url) + '" target="_blank" class="btn" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">Zur GitHub-Seite (herunterladen)</a>' +
                            '</div>';
                    } else {
                        resultDiv.innerHTML = '<div class="alert alert-success">' +
                            '<strong>&#10003; Alles aktuell!</strong> ' + escapeHtml(data.message) +
                            '</div>';
                    }
                } catch (e) {
                    resultDiv.innerHTML = '<div class="alert alert-error">Fehler beim Verarbeiten der Antwort.</div>';
                }
            } else {
                resultDiv.innerHTML = '<div class="alert alert-error">Verbindung fehlgeschlagen (HTTP ' + xhr.status + ').</div>';
            }
        }
    };
    xhr.send();
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}
</script>
</html>
