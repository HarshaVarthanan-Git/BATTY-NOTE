<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['userID'])) {
    $db = getDb();
    $session_id = session_id();

    // Mark session as inactive
    $stmt = $db->prepare('UPDATE login_sessions SET status = ? WHERE session_id = ?');
    $stmt->execute(['inactive', $session_id]);
}

// Destroy session
session_unset();
session_destroy();

// Redirect to login page
header('Location: login.php?success=You have been logged out.');
exit();
