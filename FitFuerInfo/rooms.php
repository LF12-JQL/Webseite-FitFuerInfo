<?php
// rooms.php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book') {
    $room_id = (int)$_POST['room_id'];
    $course_id = (int)$_POST['course_id'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // Basic validation
    if ($room_id && $course_id && $start_time && $end_time && $start_time < $end_time) {
        $room = getRoom($pdo, $room_id);
        $course = getCourse($pdo, $course_id);
        
        // Check capacity
        if ($course['max_participants'] > $room['workstations']) {
            $error = 'Der Raum hat nicht genügend Arbeitsplätze für diesen Kurs.';
        } else {
            // Check software
            $course_sw = getCourseSoftware($pdo, $course_id);
            $room_sw = getRoomSoftware($pdo, $room_id);
            
            $room_sw_ids = array_column($room_sw, 'id');
            $missing_sw = false;
            foreach ($course_sw as $csw) {
                if (!in_array($csw['id'], $room_sw_ids)) {
                    $missing_sw = true;
                    break;
                }
            }
            
            if ($missing_sw) {
                $error = 'Der Raum verfügt nicht über alle für den Kurs benötigten Softwarepakete.';
            } else {
                // Check overlaps
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE room_id = ? AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?))");
                $stmt->execute([$room_id, $end_time, $start_time, $end_time, $start_time]);
                if ($stmt->fetchColumn() > 0) {
                    $error = 'Der Raum ist in diesem Zeitraum bereits gebucht.';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO bookings (room_id, course_id, user_id, start_time, end_time) VALUES (?, ?, ?, ?, ?)");
                    if ($stmt->execute([$room_id, $course_id, $user_id, $start_time, $end_time])) {
                        $success = 'Raum erfolgreich gebucht.';
                    }
                }
            }
        }
    } else {
        $error = 'Ungültige Eingaben für die Buchung.';
    }
}

// Fetch data for view
$rooms = $pdo->query("SELECT * FROM rooms ORDER BY name")->fetchAll();
$courses = $pdo->query("SELECT * FROM courses ORDER BY title")->fetchAll();
?>

<div class="card">
    <h2>Räume und Buchungen</h2>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div style="display: flex; gap: 2rem;">
        <!-- Room List -->
        <div style="flex: 1;">
            <h3>Unsere Räume</h3>
            <?php foreach ($rooms as $r): ?>
                <div style="border: 1px solid var(--border-color); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                    <h4><?= htmlspecialchars($r['name']) ?></h4>
                    <p>Arbeitsplätze: <?= $r['workstations'] ?></p>
                    <p style="font-size: 0.9rem; color: var(--text-muted);">
                        Software: 
                        <?php 
                        $sw = getRoomSoftware($pdo, $r['id']);
                        echo $sw ? implode(', ', array_column($sw, 'name')) : 'Keine spezifische Software'; 
                        ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Booking Form -->
        <div style="flex: 1;">
            <div class="card" style="margin-bottom: 0;">
                <h3>Raum buchen</h3>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="book">
                    
                    <div class="form-group">
                        <label for="room_id">Raum auswählen</label>
                        <select id="room_id" name="room_id" class="form-control" required>
                            <option value="">-- Bitte wählen --</option>
                            <?php foreach ($rooms as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?> (<?= $r['workstations'] ?> Plätze)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="course_id">Kurs auswählen</label>
                        <select id="course_id" name="course_id" class="form-control" required>
                            <option value="">-- Bitte wählen --</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?> (Max: <?= $c['max_participants'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="start_time">Beginn</label>
                        <input type="datetime-local" id="start_time" name="start_time" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="end_time">Ende</label>
                        <input type="datetime-local" id="end_time" name="end_time" class="form-control" required>
                    </div>

                    <button type="submit" class="btn">Buchen</button>
                </form>
            </div>
        </div>
    </div>
</div>

</div>
</body>
</html>
