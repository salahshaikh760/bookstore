<?php
// Start session to ensure navbar works correctly
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us - Online Bookstore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

<?php include 'navbar.php'; ?>

<div class="bg-primary text-white text-center py-5 mb-5">
    <div class="container">
        <h1 class="display-4 fw-bold">About the Bookstore</h1>
        <p class="lead">Your one-stop digital destination for books across all genres.</p>
    </div>
</div>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    
                    <h3 class="text-primary mb-3"><i class="fas fa-book-open"></i> Our Story</h3>
                    <p class="text-muted">
                        Welcome to the <strong>Online Bookstore</strong>! This platform was designed to bring a seamless, user-friendly book shopping experience to the web. 
                        Whether you are looking for classic literature, modern fiction, or educational resources, we aim to provide a clean and secure environment to browse and purchase your favorite titles.
                    </p>
                    
                    <hr class="my-4">

                    <h4 class="mb-3"><i class="fas fa-code"></i> The Developer</h4>
                    <p>
                        This project was architected and developed by <strong>Mohd Salah</strong>. 
                        It serves as a comprehensive demonstration of Full-Stack Web Development, integrating database management with a responsive front-end interface.
                    </p>

                    <hr class="my-4">

                    <h4 class="mb-3"><i class="fas fa-layer-group"></i> Tech Stack</h4>
                    <p>This application relies on robust technologies to ensure performance and security:</p>
                    <div class="mb-4">
                        <span class="badge bg-dark p-2 me-1">PHP 8</span>
                        <span class="badge bg-warning text-dark p-2 me-1">MySQL</span>
                        <span class="badge bg-primary p-2 me-1">Bootstrap 5</span>
                        <span class="badge bg-danger p-2 me-1">HTML5 / CSS3</span>
                        <span class="badge bg-info text-dark p-2 me-1">JavaScript</span>
                    </div>

                    <div class="alert alert-light border text-center mt-4">
                        <p class="mb-2"><strong>Want to explore our collection?</strong></p>
                        <a href="index.php" class="btn btn-outline-primary">Browse Books</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>