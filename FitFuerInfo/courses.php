<?php
// courses.php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $title = trim($_POST['title']);
    $max_participants = (int)$_POST['max_participants'];
    $software = isset($_POST['software']) ? $_POST['software'] : [];

    if (!empty($title) && $max_participants > 0) {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO courses (title, max_participants, creator_id) VALUES (?, ?, ?)");
            $stmt->execute([$title, $max_participants, $user_id]);
            $course_id = $pdo->lastInsertId();

            foreach ($software as $soft_id) {
                $stmt = $pdo->prepare("INSERT INTO course_software (course_id, software_id) VALUES (?, ?)");
                $stmt->execute([$course_id, $soft_id]);
            }
            $pdo->commit();
            $success = 'Kursprofil erfolgreich erstellt.';
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Fehler beim Erstellen des Kurses.';
        }
    } else {
        $error = 'Bitte geben Sie Titel und eine gültige Teilnehmerzahl an.';
    }
}

$stmt = $pdo->query("SELECT c.*, u.username as creator_name FROM courses c JOIN users u ON c.creator_id = u.id ORDER BY c.title");
$all_courses = $stmt->fetchAll();

$software_list = getSoftwareList($pdo);
?>

<div class="card">
    <h2>Kurse verwalten</h2>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <table style="margin-top: 1rem;">
        <thead>
            <tr>
                <th>Titel</th>
                <th>Teilnehmer max.</th>
                <th>Ersteller</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($all_courses as $c): 
                $can_edit = ($c['creator_id'] == $user_id) || isCourseOwner($pdo, $c['id'], $user_id) || isSystemverwalter();
            ?>
                <tr>
                    <td><?= htmlspecialchars($c['title']) ?></td>
                    <td><?= $c['max_participants'] ?></td>
                    <td><?= htmlspecialchars($c['creator_name']) ?></td>
                    <td>
                        <a href="course_edit.php?id=<?= $c['id'] ?>" class="btn <?= $can_edit ? '' : 'btn-secondary' ?>">
                            <?= $can_edit ? 'Bearbeiten' : 'Ansehen' ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h3>Neuen Kurs erstellen</h3>
    <form method="POST" action="">
        <input type="hidden" name="action" value="create">
        <div class="form-group">
            <label for="title">Kurstitel</label>
            <input type="text" id="title" name="title" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="max_participants">Maximale Teilnehmerzahl</label>
            <input type="number" id="max_participants" name="max_participants" class="form-control" min="1" required>
        </div>
        <div class="form-group">
            <label>Benötigte Software</label>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <?php foreach ($software_list as $s): ?>
                    <label style="font-weight: normal;">
                        <input type="checkbox" name="software[]" value="<?= $s['id'] ?>"> <?= htmlspecialchars($s['name']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <button type="submit" class="btn">Kurs erstellen</button>
    </form>
</div>

</div>
</body>
</html>
