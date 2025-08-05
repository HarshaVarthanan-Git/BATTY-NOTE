<?php
session_start();
require_once 'db.php';

function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        header('Location: register.php?error=Username and password required');
        exit();
    }

    $db = getDb();
    $stmt = $db->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute([$username]);

    if ($stmt->fetch()) {
        header('Location: register.php?error=Username already exists');
        exit();
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $user_token = generateToken();

    $stmt = $db->prepare('INSERT INTO users (username, password, user_token, date) VALUES (?, ?, ?, NOW())');
    $stmt->execute([$username, $hash, $user_token]);

    $userID = $db->lastInsertId();
    $_SESSION['userID'] = $userID;
    $_SESSION['username'] = $username;

    header('Location: login.php?success=Account created!');
    exit();
} else {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Batty Notes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --secondary: #0ea5e9;
            --text: #1e293b;
            --gray: #94a3b8;
            --bg: #f8fafc;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: var(--bg);
            color: var(--text);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .header {
            padding: 2rem 1rem 1rem;
            text-align: center;
        }

        .header .app-name {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header .slogan {
            font-size: 1rem;
            color: var(--gray);
            margin-top: 0.3rem;
        }

        .container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .card {
            background: var(--white);
            padding: 2rem 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 400px;
            animation: fadeIn 0.6s ease;
        }

        .card h2 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .card p {
            color: var(--gray);
            margin-bottom: 1.5rem;
        }

        .input-group {
            position: relative;
            margin-bottom: 1.2rem;
        }

        .input-group i {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: var(--primary);
        }

        .input-group input {
            width: 100%;
            padding: 0.75rem 0.75rem 0.75rem 2.5rem;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            outline: none;
            transition: border-color 0.3s;
        }

        .input-group input:focus {
            border-color: var(--primary);
        }

        .alert {
            background: #fee2e2;
            color: #b91c1c;
            padding: 0.6rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }

        .btn {
            background: var(--primary);
            color: white;
            border: none;
            width: 100%;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #4338ca;
        }

        .extras {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.95rem;
            color: var(--gray);
        }

        .extras a {
            color: var(--primary);
            text-decoration: none;
            margin-left: 0.3rem;
        }

        .footer {
            text-align: center;
            padding: 1rem;
            font-size: 0.9rem;
            color: var(--gray);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 500px) {
            .card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="app-name">BATTY NOTES</div>
        <div class="slogan">vent yourself out</div>
    </div>

    <div class="container">
        <div class="card">
            <h2>Create Account</h2>
            <p>Sign up with a unique username and a secure password</p>
            <?php if (isset($_GET['error'])): ?>
                <div class="alert"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>
            <form method="POST" action="register.php" autocomplete="off">
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="Choose a username" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn">Sign Up</button>
            </form>
            <div class="extras">
                <p>Already have an account?<a href="login.php"> Sign in</a></p>
            </div>
        </div>
    </div>

    <div class="footer">&copy; <?php echo date('Y'); ?> Batty Notes. Made with &hearts;</div>
</body>
</html>
<?php } ?>
