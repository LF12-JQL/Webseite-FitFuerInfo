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

<div class="card" style="text-align: center; padding: 3rem 2rem; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; border: none;">
    <h2 style="margin: 0 0 0.5rem 0; font-size: 2rem;">👋 Willkommen bei FitFuerInfo</h2>
    <p style="margin: 0; font-size: 1.1rem; opacity: 0.9;">Hier können Sie Ihre Kursprofile verwalten und Räume effizient buchen.</p>
</div>

<div style="display: flex; gap: 2rem;">
    <div class="card" style="flex: 1;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="margin: 0;">📅 Meine anstehenden Buchungen</h3>
            <a href="rooms.php" class="btn" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">+ Neue Buchung</a>
        </div>
        
        <?php if (count($my_bookings) > 0): ?>
            <div style="overflow-x: auto;">
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
                                <td><strong><?= htmlspecialchars($b['room_name']) ?></strong></td>
                                <td><?= htmlspecialchars($b['course_title']) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($b['start_time'])) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($b['end_time'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <span class="icon">🛋️</span>
                <strong>Keine Buchungen</strong>
                <p style="font-size: 0.9rem; margin-top: 0.5rem;">Sie haben derzeit keine Räume reserviert.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="card" style="flex: 1;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="margin: 0;">📚 Meine Kursprofile</h3>
            <a href="courses.php" class="btn btn-secondary" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">Verwalten</a>
        </div>
        
        <?php if (count($my_courses) > 0): ?>
            <ul class="dashboard-list">
                <?php foreach ($my_courses as $c): ?>
                    <li>
                        <span style="font-size: 1.2rem;">📘</span>
                        <strong><?= htmlspecialchars($c['title']) ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="empty-state">
                <span class="icon">📝</span>
                <strong>Keine Kurse</strong>
                <p style="font-size: 0.9rem; margin-top: 0.5rem;">Sie verwalten noch keine Kursprofile.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</div>
</body>
</html>
