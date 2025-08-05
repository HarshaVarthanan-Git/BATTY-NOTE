<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}

$db = getDb();
$note_id = $_GET['id'] ?? null;

if (!$note_id) {
    header('Location: dashboard.php?error=Note not found');
    exit();
}

$stmt = $db->prepare('SELECT * FROM user_notes WHERE id = ? AND user_id = ?');
$stmt->execute([$note_id, $_SESSION['userID']]);
$note = $stmt->fetch();

if (!$note) {
    header('Location: dashboard.php?error=Note not found');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '' || $content === '') {
        header("Location: edit_note.php?id=$note_id&error=Title and content are required");
        exit();
    }

    $stmt = $db->prepare('UPDATE user_notes SET title = ?, content = ?, date_modified = NOW() WHERE id = ? AND user_id = ?');
    $stmt->execute([$title, $content, $note_id, $_SESSION['userID']]);

    header('Location: dashboard.php?success=Note updated');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Note - Batty Notes</title>
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

        .back-btn {
            background: var(--primary);
            color: white;
            padding: 0.4rem 1rem;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
        }

        .back-btn:hover {
            background: #4338ca;
        }

        .container {
            max-width: 600px;
            margin: 3rem auto;
            padding: 2rem;
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }

        h2 {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        input, textarea {
            padding: 0.8rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 1rem;
            resize: vertical;
        }

        textarea {
            min-height: 150px;
        }

        button {
            background: var(--secondary);
            color: white;
            border: none;
            padding: 0.8rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
        }

        button:hover {
            background: #0284c7;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="app-name">BATTY NOTES</div>
        <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <div class="container">
        <h2>Edit Note</h2>
        <?php if (isset($_GET['error'])): ?>
            <div class="error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        <form method="POST" action="edit_note.php?id=<?php echo $note_id; ?>">
            <input type="text" name="title" value="<?php echo htmlspecialchars($note['title']); ?>" required>
            <textarea name="content" required><?php echo htmlspecialchars($note['content']); ?></textarea>
            <button type="submit">Update Note</button>
        </form>
    </div>
</body>
</html>
