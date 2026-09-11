<?php
// rooms.php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book') {
    $room_ids = isset($_POST['room_ids']) ? $_POST['room_ids'] : [];
    $course_id = (int)$_POST['course_id'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // Basic validation
    if (!empty($room_ids) && is_array($room_ids) && $course_id && $start_time && $end_time && $start_time < $end_time) {
        $course = getCourse($pdo, $course_id);
        $course_sw = getCourseSoftware($pdo, $course_id);
        
        $total_workstations = 0;
        $rooms_data = [];
        
        foreach ($room_ids as $r_id) {
            $r_id = (int)$r_id;
            $r_data = getRoom($pdo, $r_id);
            if ($r_data) {
                $total_workstations += $r_data['workstations'];
                $rooms_data[] = $r_data;
            }
        }
        
        // Check capacity across all selected rooms
        if ($course['max_participants'] > $total_workstations) {
            $error = 'Die ausgewählten Räume haben in Summe nicht genügend Arbeitsplätze für diesen Kurs.';
        } else {
            // Check software for EACH selected room
            $missing_sw = false;
            foreach ($rooms_data as $r_data) {
                $room_sw = getRoomSoftware($pdo, $r_data['id']);
                $room_sw_ids = array_column($room_sw, 'id');
                
                foreach ($course_sw as $csw) {
                    if (!in_array($csw['id'], $room_sw_ids)) {
                        $missing_sw = true;
                        break 2; // Break out of both loops
                    }
                }
            }
            
            if ($missing_sw) {
                $error = 'Einer oder mehrere der ausgewählten Räume verfügen nicht über alle für den Kurs benötigten Softwarepakete.';
            } else {
                // Check overlaps for EACH selected room
                $overlap = false;
                foreach ($rooms_data as $r_data) {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE room_id = ? AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?))");
                    $stmt->execute([$r_data['id'], $end_time, $start_time, $end_time, $start_time]);
                    if ($stmt->fetchColumn() > 0) {
                        $overlap = true;
                        break;
                    }
                }
                
                if ($overlap) {
                    $error = 'Einer der ausgewählten Räume ist in diesem Zeitraum bereits gebucht.';
                } else {
                    // All checks passed, insert bookings
                    $pdo->beginTransaction();
                    try {
                        $stmt = $pdo->prepare("INSERT INTO bookings (room_id, course_id, user_id, start_time, end_time) VALUES (?, ?, ?, ?, ?)");
                        foreach ($rooms_data as $r_data) {
                            $stmt->execute([$r_data['id'], $course_id, $user_id, $start_time, $end_time]);
                        }
                        $pdo->commit();
                        $success = 'Räume erfolgreich gebucht.';
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $error = 'Fehler beim Buchen der Räume.';
                    }
                }
            }
        }
    } else {
        $error = 'Ungültige Eingaben für die Buchung (Bitte mindestens einen Raum wählen).';
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
                        <label>Räume auswählen (mehrere möglich)</label>
                        <input type="text" id="room-search" class="form-control" placeholder="🔍 Raum suchen (Name, Plätze...)" style="margin-bottom: 0.5rem;">
                        <small id="room-search-count" style="color: var(--text-muted); display: block; margin-bottom: 0.25rem;"><?= count($rooms) ?> Räume verfügbar</small>
                        <div id="room-list" style="border: 1px solid var(--border-color); padding: 0.5rem; border-radius: 0.375rem; max-height: 150px; overflow-y: auto;">
                            <?php foreach ($rooms as $r): ?>
                                <label class="room-item" style="display: block; font-weight: normal; margin-bottom: 0.25rem;" data-search="<?= htmlspecialchars(strtolower($r['name'] . ' ' . $r['workstations'] . ' plätze')) ?>">
                                    <input type="checkbox" name="room_ids[]" value="<?= $r['id'] ?>"> 
                                    <?= htmlspecialchars($r['name']) ?> (<?= $r['workstations'] ?> Plätze)
                                </label>
                            <?php endforeach; ?>
                            <div id="room-no-results" style="display: none; color: var(--text-muted); padding: 0.5rem; text-align: center;">Keine Räume gefunden.</div>
                        </div>
                    </div>

                    <script>
                    (function() {
                        var searchInput = document.getElementById('room-search');
                        var roomItems = document.querySelectorAll('.room-item');
                        var noResults = document.getElementById('room-no-results');
                        var countEl = document.getElementById('room-search-count');
                        var total = roomItems.length;

                        searchInput.addEventListener('input', function() {
                            var query = this.value.toLowerCase().trim();
                            var visible = 0;

                            for (var i = 0; i < roomItems.length; i++) {
                                var item = roomItems[i];
                                var searchText = item.getAttribute('data-search');
                                if (query === '' || searchText.indexOf(query) !== -1) {
                                    item.style.display = 'block';
                                    visible++;
                                } else {
                                    item.style.display = 'none';
                                }
                            }

                            noResults.style.display = visible === 0 ? 'block' : 'none';

                            if (query === '') {
                                countEl.textContent = total + ' Räume verfügbar';
                            } else {
                                countEl.textContent = visible + ' von ' + total + ' Räumen';
                            }
                        });
                    })();
                    </script>

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
