<?php
session_start();
include 'db.php';
include 'csrf.php';

$message      = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. CSRF check
    csrf_verify();

    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    // 2. Server-side password strength validation
    $errors = [];
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }
    if (!preg_match('/[A-Za-z]/', $password)) {
        $errors[] = "Password must contain at least one letter.";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number.";
    }
    if (strlen($name) < 2) {
        $errors[] = "Please enter your full name.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (!empty($errors)) {
        $message      = implode("<br>", array_map('htmlspecialchars', $errors));
        $message_type = "danger";
    } else {
        // 3. Check if email already exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            // SECURITY: Vague message to avoid user enumeration
            $message      = "If this email is not registered, your account has been created. <a href='login.php' class='alert-link'>Login here</a>.";
            $message_type = "success";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($stmt->execute()) {
                $message      = "Account created successfully! <a href='login.php' class='alert-link'>Login here</a>.";
                $message_type = "success";
            } else {
                error_log("Register error: " . $stmt->error);
                $message      = "An error occurred. Please try again.";
                $message_type = "danger";
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Account - LuminaBooks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .strength-bar { height: 4px; border-radius: 2px; transition: all 0.3s; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-plus text-primary fa-3x mb-3"></i>
                        <h3 class="fw-bold">Create Account</h3>
                        <p class="text-muted small">Join our community of book lovers</p>
                    </div>

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $message_type; ?> text-center small">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <form action="register.php" method="POST" id="registerForm">
                        <?php csrf_field(); ?>

                        <div class="mb-3">
                            <label class="form-label small text-muted">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                                <input type="text" name="name" class="form-control border-start-0 ps-0" placeholder="John Doe" required minlength="2">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                                <input type="email" name="email" class="form-control border-start-0 ps-0" placeholder="name@example.com" required>
                            </div>
                        </div>

                        <div class="mb-1">
                            <label class="form-label small text-muted">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                                <input type="password" name="password" id="password" class="form-control border-start-0 ps-0"
                                       placeholder="Min 8 chars, 1 letter, 1 number" required minlength="8"
                                       oninput="checkStrength(this.value)">
                            </div>
                        </div>
                        <!-- Password strength indicator -->
                        <div class="mb-4">
                            <div class="d-flex gap-1 mt-2">
                                <div class="strength-bar flex-fill bg-secondary" id="s1"></div>
                                <div class="strength-bar flex-fill bg-secondary" id="s2"></div>
                                <div class="strength-bar flex-fill bg-secondary" id="s3"></div>
                                <div class="strength-bar flex-fill bg-secondary" id="s4"></div>
                            </div>
                            <small id="strength-label" class="text-muted"></small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm">
                            SIGN UP
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <small class="text-muted">Already have an account? <a href="login.php" class="text-primary text-decoration-none fw-bold">Login</a></small>
                    </div>
                </div>
            </div>
            <div class="text-center mt-3">
                <a href="index.php" class="text-secondary small text-decoration-none">← Back to Homepage</a>
            </div>
        </div>
    </div>
</div>

<script>
function checkStrength(pw) {
    const bars   = ['s1','s2','s3','s4'];
    const label  = document.getElementById('strength-label');
    const colors = ['bg-danger','bg-warning','bg-info','bg-success'];
    const labels = ['Weak','Fair','Good','Strong'];

    let score = 0;
    if (pw.length >= 8)                     score++;
    if (/[A-Z]/.test(pw))                   score++;
    if (/[0-9]/.test(pw))                   score++;
    if (/[^A-Za-z0-9]/.test(pw))            score++;

    bars.forEach((id, i) => {
        const el = document.getElementById(id);
        el.className = 'strength-bar flex-fill ' + (i < score ? colors[score - 1] : 'bg-secondary');
    });
    label.textContent = score > 0 ? labels[score - 1] : '';
}
</script>
</body>
</html>
