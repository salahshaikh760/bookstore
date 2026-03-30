<?php
session_start();
include 'db.php';
include 'csrf.php';

$book_id = isset($_REQUEST['book_id']) ? intval($_REQUEST['book_id']) : 0;
if ($book_id == 0) {
    header("Location: index.php");
    exit();
}

$review_msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    // SECURITY: Validate CSRF before processing review
    csrf_verify();

    $user_id = $_SESSION['user_id'];
    $rating  = intval($_POST['rating']);
    // Clamp rating to valid range
    $rating  = max(1, min(5, $rating));
    $comment = trim($_POST['comment']);

    $check = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND book_id = ?");
    $check->bind_param("ii", $user_id, $book_id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $review_msg = "<div class='alert alert-warning'>You have already reviewed this book.</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO reviews (user_id, book_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $user_id, $book_id, $rating, $comment);
        if ($stmt->execute()) {
            $review_msg = "<div class='alert alert-success'>Review submitted successfully!</div>";
        } else {
            $review_msg = "<div class='alert alert-danger'>Error submitting review.</div>";
        }
    }
}

$stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();
if (!$book) die("Book not found.");

$orig     = $book['original_price'];
$curr     = $book['price'];
$savings  = $orig - $curr;
$discount = ($orig > 0) ? round(($savings / $orig) * 100) : 0;

$reviews_query = $conn->prepare("SELECT r.*, u.name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.book_id = ? ORDER BY r.created_at DESC");
$reviews_query->bind_param("i", $book_id);
$reviews_query->execute();
$reviews_result = $reviews_query->get_result();

$avg_query = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM reviews WHERE book_id = ?");
$avg_query->bind_param("i", $book_id);
$avg_query->execute();
$rating_data  = $avg_query->get_result()->fetch_assoc();
$avg_rating   = round($rating_data['avg_rating'] ?? 0, 1);
$total_reviews = $rating_data['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo htmlspecialchars($book['title']); ?> - Offer Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .book-cover-lg { max-height: 500px; width: 100%; object-fit: contain; background: #f8f9fa; border-radius: 10px; padding: 20px; }
        .rating-star { color: #fbbf24; }
        .trust-badge { font-size: 0.9rem; font-weight: 500; color: #555; }
        .trust-badge i { font-size: 1.1rem; margin-right: 6px; }
        .review-card { border-bottom: 1px solid #e2e8f0; padding: 20px 0; }
        .review-card:last-child { border-bottom: none; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container py-5">
    <div class="card border-0 shadow-lg overflow-hidden mb-5">
        <div class="row g-0">
            <div class="col-md-5 bg-light d-flex align-items-center justify-content-center p-4">
                <img src="<?php echo htmlspecialchars($book['image']); ?>"
                     class="book-cover-lg shadow-sm"
                     alt="<?php echo htmlspecialchars($book['title']); ?>"
                     onerror="this.src='https://placehold.co/400x600?text=No+Cover'">
            </div>
            <div class="col-md-7">
                <div class="card-body p-5">
                    <h1 class="fw-bold mb-2"><?php echo htmlspecialchars($book['title']); ?></h1>
                    <p class="text-muted fs-5 mb-2">by <strong><?php echo htmlspecialchars($book['author']); ?></strong></p>
                    <div class="mb-3">
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                            <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($book['genre'] ?? 'General'); ?>
                        </span>
                    </div>
                    <div class="mb-4 d-flex align-items-center">
                        <span class="h4 mb-0 me-2 fw-bold text-primary"><?php echo $avg_rating; ?></span>
                        <div class="me-2">
                            <?php for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $avg_rating ? '<i class="fas fa-star rating-star"></i>' : '<i class="far fa-star text-muted"></i>';
                            } ?>
                        </div>
                        <span class="text-muted small">(<?php echo $total_reviews; ?> reviews)</span>
                    </div>
                    <hr class="text-muted opacity-25">
                    <div class="mb-4">
                        <div class="d-flex align-items-center">
                            <?php if ($discount > 0): ?>
                                <span class="text-danger fw-bold fs-5 me-2">-<?php echo $discount; ?>%</span>
                            <?php endif; ?>
                            <h2 class="fw-bold mb-0">₹<?php echo number_format($curr, 2); ?></h2>
                        </div>
                        <?php if ($orig > $curr): ?>
                            <div class="text-muted">M.R.P.: <span class="text-decoration-line-through">₹<?php echo number_format($orig, 2); ?></span></div>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex flex-wrap gap-4 mb-4">
                        <div class="trust-badge text-success"><i class="fas fa-check-circle"></i> In Stock</div>
                        <div class="trust-badge text-primary"><i class="fas fa-truck-fast"></i> Fast Delivery</div>
                        <div class="trust-badge text-secondary"><i class="fas fa-lock"></i> Secure Transaction</div>
                    </div>
                    <div class="mb-5">
                        <h5 class="fw-bold">About this item</h5>
                        <p class="text-muted" style="line-height: 1.7;"><?php echo nl2br(htmlspecialchars($book['summary'])); ?></p>
                    </div>
                    <form method="POST" action="cart.php">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                        <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill shadow-sm">
                            <i class="fas fa-cart-plus me-2"></i> Add to Cart
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h3 class="fw-bold mb-4">Customer Reviews</h3>
            <?php echo $review_msg; ?>
            <div class="card mb-5 border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Write a Review</h5>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form method="POST">
                            <?php csrf_field(); ?>
                            <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold">Your Rating</label>
                                <select name="rating" class="form-select w-auto" required>
                                    <option value="5">⭐⭐⭐⭐⭐ - Excellent</option>
                                    <option value="4">⭐⭐⭐⭐ - Good</option>
                                    <option value="3">⭐⭐⭐ - Average</option>
                                    <option value="2">⭐⭐ - Poor</option>
                                    <option value="1">⭐ - Terrible</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold">Your Review</label>
                                <textarea name="comment" class="form-control" rows="3" placeholder="What did you think about this book?" required></textarea>
                            </div>
                            <button type="submit" name="submit_review" class="btn btn-primary px-4">Submit Review</button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">Please <a href="login.php" class="alert-link">login</a> to write a review.</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <?php if ($reviews_result->num_rows > 0): ?>
                        <?php while ($r = $reviews_result->fetch_assoc()): ?>
                            <div class="review-card">
                                <div class="d-flex justify-content-between mb-2">
                                    <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($r['name']); ?></h6>
                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($r['created_at'])); ?></small>
                                </div>
                                <div class="mb-2">
                                    <?php for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= $r['rating'] ? '<i class="fas fa-star rating-star small"></i>' : '<i class="far fa-star text-muted small"></i>';
                                    } ?>
                                </div>
                                <p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars($r['comment'])); ?></p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="far fa-comment-dots fa-3x mb-3 opacity-50"></i>
                            <p>No reviews yet. Be the first to review this book!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
