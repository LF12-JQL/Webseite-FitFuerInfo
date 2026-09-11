<?php
// room_edit.php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';
requireSystemverwalter();

$success = '';
$error = '';

if (!isset($_GET['id'])) {
    die("Raum-ID fehlt.");
}
$room_id = (int)$_GET['id'];
$room = getRoom($pdo, $room_id);

if (!$room) {
    die("Raum nicht gefunden.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update') {
        $name = trim($_POST['name']);
        $workstations = (int)$_POST['workstations'];
        $software = isset($_POST['software']) ? $_POST['software'] : [];

        if (!empty($name) && $workstations > 0) {
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("UPDATE rooms SET name = ?, workstations = ? WHERE id = ?");
                $stmt->execute([$name, $workstations, $room_id]);

                // Update software
                $pdo->prepare("DELETE FROM room_software WHERE room_id = ?")->execute([$room_id]);
                foreach ($software as $soft_id) {
                    $stmt = $pdo->prepare("INSERT INTO room_software (room_id, software_id) VALUES (?, ?)");
                    $stmt->execute([$room_id, $soft_id]);
                }
                
                $pdo->commit();
                $success = 'Raum aktualisiert.';
                $room = getRoom($pdo, $room_id); // reload
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Fehler beim Aktualisieren des Raums.';
            }
        } else {
            $error = 'Bitte gültigen Namen und Arbeitsplätze angeben.';
        }
    } elseif ($_POST['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
        if ($stmt->execute([$room_id])) {
            header("Location: admin.php");
            exit;
        }
    }
}

$all_software = getSoftwareList($pdo);
$room_sw_raw = getRoomSoftware($pdo, $room_id);
$room_sw_ids = array_column($room_sw_raw, 'id');
?>

<div class="card">
    <h2>Raum bearbeiten: <?= htmlspecialchars($room['name']) ?></h2>
    <a href="admin.php" class="btn btn-secondary" style="margin-bottom: 1rem;">Zurück</a>
    
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="action" value="update">
        <div class="form-group">
            <label for="name">Raumname</label>
            <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($room['name']) ?>" required>
        </div>
        <div class="form-group">
            <label for="workstations">Arbeitsplätze</label>
            <input type="number" id="workstations" name="workstations" class="form-control" value="<?= $room['workstations'] ?>" min="1" required>
        </div>
        <div class="form-group">
            <label>Installierte Software</label>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <?php foreach ($all_software as $s): ?>
                    <label style="font-weight: normal;">
                        <input type="checkbox" name="software[]" value="<?= $s['id'] ?>" <?= in_array($s['id'], $room_sw_ids) ? 'checked' : '' ?>> <?= htmlspecialchars($s['name']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <button type="submit" class="btn">Änderungen speichern</button>
    </form>

    <div style="margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
        <h3>Gefahrenzone</h3>
        <form method="POST" action="" onsubmit="return confirm('Möchten Sie diesen Raum wirklich löschen? Alle zugehörigen Buchungen gehen verloren!');">
            <input type="hidden" name="action" value="delete">
            <button type="submit" class="btn" style="background-color: var(--error-color);">Raum löschen</button>
        </form>
    </div>
</div>
</body>
</html>
