<?php
// index.php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$user_id = $_SESSION['user_id'];

// Get user's bookings
$stmt = $pdo->prepare("
    SELECT b.*, r.name as room_name, c.title as course_title 
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    JOIN courses c ON b.course_id = c.id
    WHERE b.user_id = ?
    ORDER BY b.start_time ASC
");
$stmt->execute([$user_id]);
$my_bookings = $stmt->fetchAll();

// Get user's courses
$stmt = $pdo->prepare("
    SELECT c.* FROM courses c
    LEFT JOIN course_owners co ON c.id = co.course_id
    WHERE c.creator_id = ? OR co.user_id = ?
    GROUP BY c.id
");
$stmt->execute([$user_id, $user_id]);
$my_courses = $stmt->fetchAll();
?>

<div class="card">
    <h2>Willkommen bei FitFuerInfo</h2>
    <p>Hier können Sie Kursprofile anlegen und Räume buchen.</p>
</div>

<div style="display: flex; gap: 2rem;">
    <div class="card" style="flex: 1;">
        <h3>Meine anstehenden Buchungen</h3>
        <?php if (count($my_bookings) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Raum</th>
                        <th>Kurs</th>
                        <th>Von</th>
                        <th>Bis</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($my_bookings as $b): ?>
                        <tr>
                            <td><?= htmlspecialchars($b['room_name']) ?></td>
                            <td><?= htmlspecialchars($b['course_title']) ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($b['start_time'])) ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($b['end_time'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="margin-top: 1rem; color: var(--text-muted);">Sie haben keine aktiven Buchungen.</p>
        <?php endif; ?>
        <br>
        <a href="rooms.php" class="btn">Neuen Raum buchen</a>
    </div>

    <div class="card" style="flex: 1;">
        <h3>Meine Kursprofile</h3>
        <?php if (count($my_courses) > 0): ?>
            <ul>
                <?php foreach ($my_courses as $c): ?>
                    <li style="margin-bottom: 0.5rem;"><?= htmlspecialchars($c['title']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p style="margin-top: 1rem; color: var(--text-muted);">Sie verwalten noch keine Kurse.</p>
        <?php endif; ?>
        <br>
        <a href="courses.php" class="btn">Kursverwaltung</a>
    </div>
</div>

</div>
</body>
</html>
