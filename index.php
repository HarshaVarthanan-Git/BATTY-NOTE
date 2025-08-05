<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}
$db = getDb();
$userID = $_SESSION['userID'];
$username = $_SESSION['username'];
// Handle new note submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_note'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    if ($title !== '' && $content !== '') {
        $stmt = $db->prepare('INSERT INTO user_notes (user_id, title, content, date_created, date_modified) VALUES (?, ?, ?, NOW(), NOW())');
        $stmt->execute([$userID, $title, $content]);
        header('Location: index.php?success=Note added!');
        exit();
    } else {
        header('Location: index.php?error=Title and content required');
        exit();
    }
}
// Handle note delete
if (isset($_GET['delete'])) {
    $note_id = intval($_GET['delete']);
    $stmt = $db->prepare('DELETE FROM user_notes WHERE id = ? AND user_id = ?');
    $stmt->execute([$note_id, $userID]);
    header('Location: index.php?success=Note deleted!');
    exit();
}
// Handle note edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_note'])) {
    $note_id = intval($_POST['note_id']);
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    if ($title !== '' && $content !== '') {
        $stmt = $db->prepare('UPDATE user_notes SET title = ?, content = ?, date_modified = NOW() WHERE id = ? AND user_id = ?');
        $stmt->execute([$title, $content, $note_id, $userID]);
        header('Location: index.php?success=Note updated!');
        exit();
    } else {
        header('Location: index.php?error=Title and content required');
        exit();
    }
}
// Fetch user notes
$stmt = $db->prepare('SELECT id, title, content, date_created, date_modified FROM user_notes WHERE user_id = ? ORDER BY date_created DESC');
$stmt->execute([$userID]);
$notes = $stmt->fetchAll();
$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>BATTY NOTES - Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link href="./dist/css/tabler.min.css" rel="stylesheet" />
    <link href="./dist/css/tabler-vendors.min.css" rel="stylesheet" />
    <link href="./dist/css/demo.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="style.css">
    <style>body { background: #f7fafd; }</style>
    <script>
    function confirmDelete(noteId) {
        if (confirm('Delete this note?')) {
            window.location = 'index.php?delete=' + noteId;
        }
    }
    </script>
</head>
<body class="d-flex flex-column" style="min-height:100vh;">
    <div class="page page-center">
        <div class="container-tight py-4">
            <div class="text-center mb-4">
                <span class="navbar-brand navbar-brand-autodark"><h1>BATTY NOTES</h1><div class="slogan">vent yourself out</div></span>
            </div>
            <div class="card card-md mb-4">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
                    <div class="text-end mb-2"><a href="logout.php" class="btn btn-outline-primary btn-sm">Logout</a></div>
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger mt-2"><?php echo htmlspecialchars($_GET['error']); ?></div>
                    <?php elseif (isset($_GET['success'])): ?>
                        <div class="alert alert-success mt-2"><?php echo htmlspecialchars($_GET['success']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card card-md mb-4">
                <div class="card-body">
                    <h3 class="card-title mb-3">Add a Note</h3>
                    <form method="POST" action="index.php" autocomplete="off">
                        <div class="form-group">
                            <input type="text" name="title" class="form-control" placeholder=" " required />
                            <label class="form-label">Title</label>
                        </div>
                        <div class="form-group">
                            <textarea name="content" class="form-control" placeholder=" " required></textarea>
                            <label class="form-label">Content</label>
                        </div>
                        <div class="form-footer">
                            <button type="submit" name="add_note" class="btn btn-primary w-100">Add Note</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="mb-4">
                <h3 class="text-center mb-3" style="color:#2563eb; font-weight:600;">Your Notes</h3>
                <?php if (empty($notes)): ?>
                    <div class="card card-md"><div class="card-body text-center text-muted">No notes yet. Start venting yourself out!</div></div>
                <?php endif; ?>
                <div class="row row-cards">
                <?php foreach ($notes as $n): ?>
                    <div class="col-12 mb-3">
                        <div class="card card-md">
                            <div class="card-body">
                                <?php if ($edit_id === $n['id']): ?>
                                    <form method="POST" action="index.php">
                                        <input type="hidden" name="note_id" value="<?php echo $n['id']; ?>">
                                        <div class="form-group">
                                            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($n['title']); ?>" required />
                                            <label class="form-label">Title</label>
                                        </div>
                                        <div class="form-group">
                                            <textarea name="content" class="form-control" required><?php echo htmlspecialchars($n['content']); ?></textarea>
                                            <label class="form-label">Content</label>
                                        </div>
                                        <div class="form-footer d-flex justify-content-between">
                                            <button type="submit" name="edit_note" class="btn btn-primary">Save</button>
                                            <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <div class="note-title" style="font-size:1.1rem; font-weight:600; color:#2563eb;"><?php echo htmlspecialchars($n['title']); ?></div>
                                    <div style="margin-bottom:0.5rem;"> <?php echo nl2br(htmlspecialchars($n['content'])); ?> </div>
                                    <div class="note-date mb-2" style="font-size:0.9rem; color:#888;">
                                        Created: <?php echo $n['date_created']; ?><?php if ($n['date_modified'] !== $n['date_created']): ?> | Modified: <?php echo $n['date_modified']; ?><?php endif; ?>
                                    </div>
                                    <div class="note-actions d-flex gap-2">
                                        <a href="index.php?edit=<?php echo $n['id']; ?>" class="btn btn-outline-primary btn-sm">Edit</a>
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmDelete(<?php echo $n['id']; ?>)">Delete</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="footer">&copy; <?php echo date('Y'); ?> BATTY NOTES. Made with &hearts;.</div>
</body>
</html> 