<?php
session_start();
include 'db.php';
include 'csrf.php';

// SECURITY: Must be admin — previously only checked login
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'admin'
) {
    header("Location: index.php");
    exit();
}

if (isset($_GET["id"])) {
    $book_id = intval($_GET["id"]);
    $token   = $_GET['token'] ?? '';

    // SECURITY: Validate CSRF-style token tied to this specific book ID
    $expected = hash_hmac('sha256', 'delete_book_' . $book_id, csrf_token());
    if (!hash_equals($expected, $token)) {
        http_response_code(403);
        die("Invalid delete token.");
    }

    // SECURITY: Prepared statement — no string interpolation
    $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
    $stmt->bind_param("i", $book_id);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: dashboard.php?msg=deleted");
        exit();
    } else {
        error_log("Delete book error: " . $stmt->error);
        $stmt->close();
        $conn->close();
        header("Location: dashboard.php?msg=error");
        exit();
    }
} else {
    header("Location: dashboard.php");
    exit();
}
