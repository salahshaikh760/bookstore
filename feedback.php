<?php
session_start();
include 'db.php';
include 'csrf.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id      = $_SESSION["user_id"];
$message_html = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    csrf_verify();

    $feedback = trim($_POST["feedback"]);

    if (!empty($feedback)) {
        $stmt = $conn->prepare("INSERT INTO feedback (user_id, feedback) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $feedback);

        if ($stmt->execute()) {
            $message_html = '
            <div class="alert alert-success d-flex align-items-center" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <div>Thank you! Your feedback has been submitted successfully.</div>
            </div>';
        } else {
            error_log("Feedback error: " . $stmt->error);
            $message_html = '
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <div>An error occurred. Please try again.</div>
            </div>';
        }
        $stmt->close();
    } else {
        $message_html = '
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <div>Feedback cannot be empty.</div>
        </div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Feedback - Online Bookstore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

<?php include 'navbar.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-white text-center py-4 border-0">
                    <i class="fas fa-comment-dots text-primary fa-3x mb-3"></i>
                    <h3 class="fw-bold text-dark">We Value Your Feedback</h3>
                    <p class="text-muted mb-0">Help us improve your shopping experience.</p>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($message_html)) echo $message_html; ?>
                    <form method="POST" action="feedback.php">
                        <?php csrf_field(); ?>
                        <div class="mb-4">
                            <label for="feedback" class="form-label fw-bold">Your Message</label>
                            <textarea name="feedback" id="feedback" class="form-control" rows="6"
                                      placeholder="Tell us what you liked or what needs improvement..." required></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                <i class="fas fa-paper-plane me-2"></i> Submit Feedback
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center bg-light py-3">
                    <a href="index.php" class="text-decoration-none text-muted">
                        <i class="fas fa-arrow-left me-1"></i> Back to Homepage
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
