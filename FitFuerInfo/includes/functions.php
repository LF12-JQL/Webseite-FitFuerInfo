<?php
// includes/functions.php
require_once 'db.php';

function getSoftwareList($pdo) {
    $stmt = $pdo->query("SELECT * FROM software ORDER BY name");
    return $stmt->fetchAll();
}

function getCourse($pdo, $course_id) {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    return $stmt->fetch();
}

function getCourseSoftware($pdo, $course_id) {
    $stmt = $pdo->prepare("SELECT s.* FROM software s JOIN course_software cs ON s.id = cs.software_id WHERE cs.course_id = ?");
    $stmt->execute([$course_id]);
    return $stmt->fetchAll();
}

function getCourseOwners($pdo, $course_id) {
    $stmt = $pdo->prepare("SELECT u.* FROM users u JOIN course_owners co ON u.id = co.user_id WHERE co.course_id = ?");
    $stmt->execute([$course_id]);
    return $stmt->fetchAll();
}

function isCourseOwner($pdo, $course_id, $user_id) {
    $stmt = $pdo->prepare("SELECT 1 FROM course_owners WHERE course_id = ? AND user_id = ?");
    $stmt->execute([$course_id, $user_id]);
    return $stmt->fetchColumn() ? true : false;
}

function getRoom($pdo, $room_id) {
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$room_id]);
    return $stmt->fetch();
}

function getRoomSoftware($pdo, $room_id) {
    $stmt = $pdo->prepare("SELECT s.* FROM software s JOIN room_software rs ON s.id = rs.software_id WHERE rs.room_id = ?");
    $stmt->execute([$room_id]);
    return $stmt->fetchAll();
}

function isRoomEditor($pdo, $room_id, $user_id) {
    $stmt = $pdo->prepare("SELECT 1 FROM room_editors WHERE room_id = ? AND user_id = ?");
    $stmt->execute([$room_id, $user_id]);
    return $stmt->fetchColumn() ? true : false;
}
?>
