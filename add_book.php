<?php
session_start();
include 'db.php';
include 'csrf.php';

// SECURITY: Require admin role — not just any logged-in user
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'admin'
) {
    header("Location: index.php");
    exit();
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    csrf_verify();

    $title  = trim($_POST["title"]);
    $author = trim($_POST["author"]);
    $price  = floatval($_POST["price"]);

    $stmt = $conn->prepare("INSERT INTO books (title, author, price) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $title, $author, $price);

    if ($stmt->execute()) {
        $msg = "<div class='alert alert-success'>Book added successfully!</div>";
    } else {
        error_log("Add book error: " . $stmt->error);
        $msg = "<div class='alert alert-danger'>Error adding book.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Book</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="d-flex align-items-center mb-4">
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm me-3"><i class="fas fa-arrow-left"></i></a>
                <h3 class="fw-bold mb-0">Add New Item</h3>
            </div>

            <div class="card border-0 p-4">
                <div class="card-body">
                    <?php echo $msg; ?>
                    <form action="add_book.php" method="POST">
                        <?php csrf_field(); ?>
                        <div class="row g-4">
                            <div class="col-12">
                                <h6 class="text-primary text-uppercase small fw-bold mb-3">Basic Information</h6>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-muted small fw-bold">Book Title</label>
                                <input type="text" name="title" class="form-control form-control-lg" placeholder="e.g. The Great Gatsby" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Author Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                                    <input type="text" name="author" class="form-control" placeholder="e.g. F. Scott Fitzgerald" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Price (INR)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">₹</span>
                                    <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="col-12"><hr class="text-muted opacity-25"></div>
                            <div class="col-12 d-flex justify-content-end">
                                <a href="dashboard.php" class="btn btn-light me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4">Save Product</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
