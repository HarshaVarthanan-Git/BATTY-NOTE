<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}

$db = getDb();
$user_id = $_SESSION['userID'];

$stmt = $db->prepare('SELECT * FROM user_notes WHERE user_id = ? ORDER BY date_modified DESC');
$stmt->execute([$user_id]);
$notes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Batty Notes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --secondary: #0ea5e9;
            --gray: #64748b;
            --text: #1e293b;
            --bg: #f8fafc;
            --white: #ffffff;
        }

        * {
            font-family: 'Inter', sans-serif;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
        }

        .header {
            background: var(--white);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }

        .app-name {
            font-size: 1.4rem;
            font-weight: 800;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .logout-btn {
            background: #ef4444;
            color: white;
            padding: 0.4rem 1rem;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
        }

        .logout-btn:hover {
            background: #dc2626;
        }

        .dashboard-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .dashboard-header h2 {
            font-size: 2rem;
            font-weight: 700;
        }

        .add-btn {
            background: var(--primary);
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .add-btn:hover {
            background: #4338ca;
        }

        .note-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .note-card {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .note-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--primary);
        }

        .note-content {
            color: var(--gray);
            font-size: 0.95rem;
            margin-bottom: 1rem;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .note-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            color: #64748b;
        }

        .note-actions a {
            margin-left: 0.6rem;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .note-actions .edit {
            color: var(--primary);
        }

        .note-actions .delete {
            color: #ef4444;
        }

        .success {
            text-align: center;
            background: #d1fae5;
            color: #065f46;
            padding: 0.6rem;
            margin-bottom: 1rem;
            border-radius: 6px;
        }

        .error {
            text-align: center;
            background: #fee2e2;
            color: #991b1b;
            padding: 0.6rem;
            margin-bottom: 1rem;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="app-name">BATTY NOTES</div>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h2>Your Notes</h2>
            <a href="add_note.php" class="add-btn"><i class="fas fa-plus"></i> Add Note</a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <?php if (empty($notes)): ?>
            <p style="text-align:center; color: var(--gray);">You have no notes yet. Click "Add Note" to create your first one.</p>
        <?php else: ?>
            <div class="note-grid">
                <?php foreach ($notes as $note): ?>
                    <div class="note-card">
                        <div>
                            <div class="note-title"><?php echo htmlspecialchars($note['title']); ?></div>
                            <div class="note-content"><?php echo nl2br(htmlspecialchars(substr($note['content'], 0, 200))); ?><?php echo strlen($note['content']) > 200 ? '...' : ''; ?></div>
                        </div>
                        <div class="note-footer">
                            <span><?php echo date('d M Y', strtotime($note['date_modified'])); ?></span>
                            <div class="note-actions">
                                <a href="edit_note.php?id=<?php echo $note['id']; ?>" class="edit">Edit</a>
                                <a href="delete_note.php?id=<?php echo $note['id']; ?>" class="delete" onclick="return confirm('Delete this note?')">Delete</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
