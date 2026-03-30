<?php
session_start();
include 'db.php';
include 'csrf.php';

// If already logged in, redirect away
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// --- SESSION REGENERATION on every login page load (prevents fixation) ---
// We regenerate only if not yet regenerated this visit
if (empty($_SESSION['login_page_init'])) {
    session_regenerate_id(true);
    $_SESSION['login_page_init'] = true;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. CSRF check
    csrf_verify();

    // 2. Rate limiting — max 5 failed attempts per 15 minutes
    $now = time();
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['login_lockout_until'] = 0;
    }

    if ($now < $_SESSION['login_lockout_until']) {
        $wait = ceil(($_SESSION['login_lockout_until'] - $now) / 60);
        $error = "Too many failed attempts. Please wait {$wait} minute(s) and try again.";
    } else {

        $email    = trim($_POST['email']);
        $password = trim($_POST['password']);

        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        $login_ok = false;
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $login_ok = true;
            }
        }

        if ($login_ok) {
            // Reset failed attempts
            $_SESSION['login_attempts']     = 0;
            $_SESSION['login_lockout_until'] = 0;

            // Regenerate session ID after successful login (session fixation prevention)
            session_regenerate_id(true);

            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            // Increment failed attempts
            $_SESSION['login_attempts']++;
            if ($_SESSION['login_attempts'] >= 5) {
                $_SESSION['login_lockout_until'] = $now + (15 * 60);
                $_SESSION['login_attempts']      = 0;
                $error = "Too many failed attempts. Your account has been locked for 15 minutes.";
            } else {
                // SECURITY: Generic message — don't reveal whether email or password was wrong
                $error = "Invalid email or password.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login - LuminaBooks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { width: 100%; max-width: 400px; padding: 40px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); background: white; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2 class="text-center fw-bold mb-4">Welcome Back</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <?php csrf_field(); ?>
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required autocomplete="email">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Sign In</button>
        </form>
        <p class="text-center mt-3 text-muted">
            New here? <a href="register.php">Create an account</a>
        </p>
    </div>
</body>
</html>

