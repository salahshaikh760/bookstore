<?php
session_start();
include 'db.php';
include 'csrf.php';

// --- 1. SECURITY CHECK — admin role required ---
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'admin'
) {
    header("Location: index.php");
    exit();
}

// --- 2. HANDLE ACTIONS ---

$msg = "";

// A. Add Book (POST)
if (isset($_POST['add_book'])) {
    csrf_verify();

    $title          = trim($_POST['title']);
    $author         = trim($_POST['author']);
    $price          = floatval($_POST['price']);
    $original_price = floatval($_POST['original_price']);
    $genre          = trim($_POST['genre']);
    $image          = trim($_POST['image']);
    $summary        = trim($_POST['summary']);

    $stmt = $conn->prepare("INSERT INTO books (title, author, price, original_price, genre, image, summary) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssddsss", $title, $author, $price, $original_price, $genre, $image, $summary);

    if ($stmt->execute()) {
        $msg = "<div class='alert alert-success'>Book added successfully!</div>";
    } else {
        error_log("Add book error: " . $stmt->error);
        $msg = "<div class='alert alert-danger'>Error adding book.</div>";
    }
}

// B. Delete Book (GET with CSRF token in URL)
// SECURITY: Use a token tied to the specific book ID to prevent CSRF via URL.
if (isset($_GET['delete_book'])) {
    $id    = intval($_GET['delete_book']);
    $token = $_GET['token'] ?? '';

    // Validate the per-action token
    $expected = hash_hmac('sha256', 'delete_book_' . $id, csrf_token());
    if (!hash_equals($expected, $token)) {
        http_response_code(403);
        die("Invalid delete token.");
    }

    // SECURITY: Use prepared statement — no string interpolation
    $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: admin.php");
    exit();
}

// --- 3. FETCH DATA ---

$rev_result  = $conn->query("SELECT SUM(total_amount) as revenue FROM orders");
$revenue     = $rev_result->fetch_assoc()['revenue'] ?? 0;
$orders_count = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$users_count  = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")->fetch_assoc()['count'];

$books_result  = $conn->query("SELECT * FROM books ORDER BY id DESC");

$orders_sql    = "SELECT o.id, u.name as customer_name, o.total_amount, o.status, o.order_date
                  FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC LIMIT 5";
$orders_result = $conn->query($orders_sql);

// Pre-compute delete tokens for each book so we can embed them in the table
$delete_tokens = [];
// We'll generate them inline in the loop below
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Panel - LuminaBooks</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-header { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: white; padding: 40px 0; margin-bottom: 40px; border-radius: 0 0 20px 20px; }
        .stat-card { border: none; border-radius: 12px; padding: 25px; background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s; height: 100%; }
        .stat-card:hover { transform: translateY(-5px); }
        .icon-box { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 15px; }
        .img-thumbnail-sm { width: 50px; height: 70px; object-fit: cover; border-radius: 4px; }
    </style>
</head>
<body style="background-color: #f1f5f9;">

<?php include 'navbar.php'; ?>

<div class="admin-header text-center">
    <div class="container">
        <h6 class="text-warning fw-bold text-uppercase">Admin Dashboard</h6>
        <h1 class="fw-bold mb-0">Store Management</h1>
    </div>
</div>

<div class="container pb-5">

    <?php echo $msg; ?>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="icon-box bg-success bg-opacity-10 text-success"><i class="fas fa-wallet"></i></div>
                <h2 class="fw-bold mb-1">₹<?php echo number_format($revenue, 2); ?></h2>
                <p class="text-muted mb-0">Total Revenue</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="icon-box bg-primary bg-opacity-10 text-primary"><i class="fas fa-book"></i></div>
                <h2 class="fw-bold mb-1"><?php echo $books_result->num_rows; ?></h2>
                <p class="text-muted mb-0">Total Books</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="icon-box bg-warning bg-opacity-10 text-warning"><i class="fas fa-users"></i></div>
                <h2 class="fw-bold mb-1"><?php echo $users_count; ?></h2>
                <p class="text-muted mb-0">Active Customers</p>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold m-0"><i class="fas fa-layer-group me-2 text-primary"></i>Book Inventory</h4>
        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addBookModal">
            <i class="fas fa-plus me-2"></i>Add New Book
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light sticky-top">
                        <tr>
                            <th class="ps-4">Cover</th>
                            <th>Title</th>
                            <th>Genre</th>
                            <th>Price</th>
                            <th>MRP</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($book = $books_result->fetch_assoc()):
                            // Generate a per-book CSRF-like delete token
                            $del_token = hash_hmac('sha256', 'delete_book_' . $book['id'], csrf_token());
                        ?>
                            <tr>
                                <td class="ps-4">
                                    <img src="<?php echo htmlspecialchars($book['image']); ?>" class="img-thumbnail-sm" onerror="this.src='https://placehold.co/50x70'">
                                </td>
                                <td class="fw-bold"><?php echo htmlspecialchars($book['title']); ?></td>
                                <td><span class="badge bg-secondary bg-opacity-10 text-secondary"><?php echo htmlspecialchars($book['genre']); ?></span></td>
                                <td class="text-success fw-bold">₹<?php echo $book['price']; ?></td>
                                <td class="text-muted text-decoration-line-through small">₹<?php echo $book['original_price']; ?></td>
                                <td class="text-end pe-4">
                                    <a href="admin.php?delete_book=<?php echo $book['id']; ?>&token=<?php echo urlencode($del_token); ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Are you sure you want to delete this book?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <h4 class="fw-bold mb-4"><i class="fas fa-shopping-bag me-2 text-primary"></i>Recent Orders</h4>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $orders_result->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td class="text-muted small"><?php echo date('M d', strtotime($order['order_date'])); ?></td>
                                <td class="fw-bold">₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td><span class="badge bg-success bg-opacity-10 text-success"><?php echo htmlspecialchars($order['status']); ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Add Book Modal -->
<div class="modal fade" id="addBookModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Add New Book</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <?php csrf_field(); ?>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Book Title</label><input type="text" name="title" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Author</label><input type="text" name="author" class="form-control" required></div>
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label">Selling Price</label><input type="number" step="0.01" name="price" class="form-control" required></div>
                        <div class="col-6 mb-3"><label class="form-label">Original Price (MRP)</label><input type="number" step="0.01" name="original_price" class="form-control"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Genre</label>
                        <select name="genre" class="form-select">
                            <option value="Technology">Technology</option>
                            <option value="Business">Business</option>
                            <option value="Fiction">Fiction</option>
                            <option value="Self-Help">Self-Help</option>
                            <option value="Biography">Biography</option>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Image URL</label><input type="text" name="image" class="form-control" placeholder="https://example.com/image.jpg" required><div class="form-text">Paste a link to the book cover image.</div></div>
                    <div class="mb-3"><label class="form-label">Summary</label><textarea name="summary" class="form-control" rows="3"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_book" class="btn btn-primary">Save Book</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
