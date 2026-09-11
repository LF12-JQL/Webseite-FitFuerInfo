<?php
// course_edit.php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

if (!isset($_GET['id'])) {
    die("Kurs-ID fehlt.");
}
$course_id = (int)$_GET['id'];
$course = getCourse($pdo, $course_id);

if (!$course) {
    die("Kurs nicht gefunden.");
}

$can_edit = ($course['creator_id'] == $user_id) || isCourseOwner($pdo, $course_id, $user_id) || isSystemverwalter();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!$can_edit) {
        $error = "Keine Berechtigung.";
    } else {
        if ($_POST['action'] === 'update') {
            $title = trim($_POST['title']);
            $max_participants = (int)$_POST['max_participants'];
            $software = isset($_POST['software']) ? $_POST['software'] : [];

            if (!empty($title) && $max_participants > 0) {
                $pdo->beginTransaction();
                try {
                    $stmt = $pdo->prepare("UPDATE courses SET title = ?, max_participants = ? WHERE id = ?");
                    $stmt->execute([$title, $max_participants, $course_id]);

                    // Update software
                    $pdo->prepare("DELETE FROM course_software WHERE course_id = ?")->execute([$course_id]);
                    foreach ($software as $soft_id) {
                        $stmt = $pdo->prepare("INSERT INTO course_software (course_id, software_id) VALUES (?, ?)");
                        $stmt->execute([$course_id, $soft_id]);
                    }
                    $pdo->commit();
                    $success = 'Kursprofil aktualisiert.';
                    $course = getCourse($pdo, $course_id); // reload
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = 'Fehler beim Aktualisieren.';
                }
            } else {
                $error = 'Bitte gültigen Titel und Teilnehmerzahl angeben.';
            }
        } elseif ($_POST['action'] === 'delete' && ($course['creator_id'] == $user_id || isSystemverwalter())) {
            $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
            if ($stmt->execute([$course_id])) {
                header("Location: courses.php");
                exit;
            }
        }
    }
}

$all_software = getSoftwareList($pdo);
$course_sw_raw = getCourseSoftware($pdo, $course_id);
$course_sw_ids = array_column($course_sw_raw, 'id');
?>

<div class="card">
    <h2>Kurs: <?= htmlspecialchars($course['title']) ?></h2>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <?php if ($can_edit): ?>
        <form method="POST" action="">
            <input type="hidden" name="action" value="update">
            <div class="form-group">
                <label for="title">Kurstitel</label>
                <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($course['title']) ?>" required>
            </div>
            <div class="form-group">
                <label for="max_participants">Maximale Teilnehmerzahl</label>
                <input type="number" id="max_participants" name="max_participants" class="form-control" value="<?= $course['max_participants'] ?>" min="1" required>
            </div>
            <div class="form-group">
                <label>Benötigte Software</label>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <?php foreach ($all_software as $s): ?>
                        <label style="font-weight: normal;">
                            <input type="checkbox" name="software[]" value="<?= $s['id'] ?>" <?= in_array($s['id'], $course_sw_ids) ? 'checked' : '' ?>> <?= htmlspecialchars($s['name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit" class="btn">Änderungen speichern</button>
        </form>

        <?php if ($course['creator_id'] == $user_id || isSystemverwalter()): ?>
            <div style="margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                <h3>Gefahrenzone</h3>
                <form method="POST" action="" onsubmit="return confirm('Kurs wirklich löschen?');">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="btn" style="background-color: var(--error-color);">Kurs löschen</button>
                </form>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <p>Titel: <?= htmlspecialchars($course['title']) ?></p>
        <p>Maximale Teilnehmer: <?= $course['max_participants'] ?></p>
        <p>Software: <?= implode(', ', array_column($course_sw_raw, 'name')) ?></p>
    <?php endif; ?>
</div>

</div>
</body>
</html>
