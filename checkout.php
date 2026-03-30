<?php
session_start();
include 'db.php';
include 'csrf.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// SECURITY: Validate CSRF token before processing the order
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
}

$user_id       = $_SESSION['user_id'];
$order_success = false;
$order_id      = 0;
$cart_books    = [];
$total         = 0;

if (!empty($_SESSION['cart'])) {
    $book_ids = implode(',', array_map('intval', $_SESSION['cart']));
    $result   = $conn->query("SELECT * FROM books WHERE id IN ($book_ids)");

    while ($book = $result->fetch_assoc()) {
        $cart_books[] = $book;
        $total        += $book['price'];
    }

    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'Completed')");
    $stmt->bind_param("id", $user_id, $total);

    if ($stmt->execute()) {
        $order_id      = $conn->insert_id;
        $order_success = true;

        $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, book_id, price) VALUES (?, ?, ?)");
        foreach ($cart_books as $book) {
            $item_stmt->bind_param("iid", $order_id, $book['id'], $book['price']);
            $item_stmt->execute();
        }

        // Clear cart only after successful order
        $_SESSION['cart'] = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Order Confirmed - LuminaBooks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-lg overflow-hidden">
                <?php if ($order_success): ?>
                    <div class="bg-success text-white text-center p-4">
                        <div class="mb-3"><i class="fas fa-check-circle fa-4x"></i></div>
                        <h2 class="fw-bold">Order Confirmed!</h2>
                        <p class="mb-0 opacity-75">Your order #<?php echo $order_id; ?> has been placed.</p>
                    </div>
                    <div class="card-body p-5">
                        <ul class="list-group list-group-flush mb-4">
                            <?php foreach ($cart_books as $book): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                    <div>
                                        <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($book['title']); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($book['author']); ?></small>
                                    </div>
                                    <span class="fw-bold">₹<?php echo number_format($book['price'], 2); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="d-flex justify-content-between align-items-center border-top pt-3 mb-4">
                            <span class="h5 mb-0 fw-bold">Total Paid</span>
                            <span class="h4 mb-0 fw-bold text-success">₹<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="dashboard.php" class="btn btn-primary btn-lg">View My Orders</a>
                            <a href="index.php" class="btn btn-outline-secondary">Continue Shopping</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card-body p-5 text-center">
                        <div class="mb-3 text-muted"><i class="fas fa-shopping-basket fa-4x"></i></div>
                        <h3>Your cart is empty</h3>
                        <p class="text-muted">It looks like you have already processed this order or have not added items yet.</p>
                        <a href="index.php" class="btn btn-primary mt-3">Browse Books</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
