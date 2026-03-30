<?php
session_start();
include 'db.php';

// Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- CRITICAL FIX: Get User Name Safely ---
// If the session has the name, use it. If not, fetch it from the database.
if (isset($_SESSION['user_name'])) {
    $user_name = $_SESSION['user_name'];
} else {
    $name_stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $name_stmt->bind_param("i", $user_id);
    $name_stmt->execute();
    $user_data = $name_stmt->get_result()->fetch_assoc();
    $user_name = $user_data ? $user_data['name'] : "Valued Customer";
}
// ------------------------------------------

// Fetch Orders for this user
$sql = "SELECT o.id, o.order_date, o.total_amount, o.status, 
               GROUP_CONCAT(b.title SEPARATOR ', ') as book_titles,
               COUNT(oi.id) as item_count
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN books b ON oi.book_id = b.id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.order_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Dashboard - LuminaBooks</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary) 0%, #3b82f6 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 40px;
            border-radius: 0 0 20px 20px;
        }
        .stat-card {
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            background: var(--card-bg);
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="dashboard-header text-center">
    <div class="container">
        <h1 class="fw-bold">Welcome back, <?php echo htmlspecialchars($user_name); ?>!</h1>
        <p class="opacity-75">Manage your orders and account details.</p>
    </div>
</div>

<div class="container pb-5">
    
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="stat-card shadow-sm">
                <h3 class="fw-bold text-primary"><?php echo $result->num_rows; ?></h3>
                <p class="text-muted mb-0">Total Orders</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card shadow-sm">
                <h3 class="fw-bold text-success">Active</h3>
                <p class="text-muted mb-0">Account Status</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card shadow-sm">
                <h3 class="fw-bold text-warning">Basic</h3>
                <p class="text-muted mb-0">Membership</p>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="fw-bold mb-0">Order History</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Order ID</th>
                            <th>Date</th>
                            <th>Books Purchased</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4 fw-bold">#<?php echo $row['id']; ?></td>
                                    <td class="text-muted small">
                                        <?php echo date('M d, Y', strtotime($row['order_date'])); ?>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 300px;" title="<?php echo htmlspecialchars($row['book_titles']); ?>">
                                            <?php echo htmlspecialchars($row['book_titles']); ?>
                                        </div>
                                        <?php if($row['item_count'] > 1): ?>
                                            <span class="badge bg-light text-secondary border">+<?php echo $row['item_count']-1; ?> more</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold text-success">₹<?php echo number_format($row['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                                            <?php echo htmlspecialchars($row['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-box-open fa-3x mb-3 opacity-50"></i>
                                        <p>No orders found. <a href="index.php">Start shopping!</a></p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>