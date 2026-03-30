<?php
session_start();
include 'db.php';

// ✅ Ensure user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// ✅ Fetch user details
$user_id = $_SESSION["user_id"];
$stmt = $conn->prepare("SELECT name, email, phone, address, genre FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $phone, $address, $genre);
$stmt->fetch();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .profile-card {
            max-width: 500px;
            margin: 80px auto;
            padding: 25px;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; margin-bottom: 20px; }
        .btn-custom { margin-top: 15px; width: 48%; }
    </style>
</head>
<body>

<div class="profile-card">
    <h2>👤 Your Profile</h2>
    <p><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
    <p><strong>Phone:</strong> <?php echo htmlspecialchars($phone); ?></p>
    <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($address)); ?></p>
    <p><strong>Preferred Genre:</strong> <?php echo htmlspecialchars($genre); ?></p>

    <div class="d-flex justify-content-between">
        <a href="dashboard.php" class="btn btn-primary btn-custom">📚 Dashboard</a>
        <a href="logout.php" class="btn btn-danger btn-custom">🚪 Logout</a>
    </div>
</div>

</body>
</html>
