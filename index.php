<?php
session_start();
include 'db.php';

// --- CONFIGURATION ---
$books_per_page = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $books_per_page;

// --- FILTER INPUTS ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$genre = isset($_GET['genre']) ? trim($_GET['genre']) : '';

// Build Query Params for Links
$query_params = "";
if($search) $query_params .= "&search=" . urlencode($search);
if($genre) $query_params .= "&genre=" . urlencode($genre);

// --- BUILD SQL QUERY ---
// We use a dynamic WHERE clause
$where_clauses = [];
$params = [];
$types = "";

if (!empty($search)) {
    $where_clauses[] = "(title LIKE ? OR author LIKE ?)";
    $search_term = "%" . $search . "%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

if (!empty($genre)) {
    $where_clauses[] = "genre = ?";
    $params[] = $genre;
    $types .= "s";
}

$sql_where = "";
if (count($where_clauses) > 0) {
    $sql_where = "WHERE " . implode(" AND ", $where_clauses);
}

// 1. Get Total Count
$count_sql = "SELECT COUNT(*) FROM books $sql_where";
$stmt = $conn->prepare($count_sql);
if(!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$stmt->bind_result($total_books);
$stmt->fetch();
$stmt->close();

$total_pages = ceil($total_books / $books_per_page);

// 2. Fetch Books
$sql = "SELECT * FROM books $sql_where LIMIT ?, ?";
// Add Limit params
$params[] = $offset;
$params[] = $books_per_page;
$types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LuminaBooks - <?php echo $genre ? htmlspecialchars($genre) : "Home"; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Reuse previous CSS */
        .hero-section {
            background: url('https://images.unsplash.com/photo-1507842217121-9e93c8aaf27c?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80') no-repeat center center/cover;
            position: relative; padding: 80px 0; margin-bottom: 3rem;
        }
        .hero-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.6); }
        .hero-content { position: relative; z-index: 1; color: white; }
        .book-card { transition: transform 0.3s ease, box-shadow 0.3s ease; height: 100%; border: 1px solid var(--border-color); position: relative; }
        .book-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-md); }
        .book-img-wrapper { height: 350px; background: #f8f9fa; border-bottom: 1px solid var(--border-color); overflow: hidden; padding: 20px; position: relative; }
        .book-link { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; text-decoration: none; }
        .book-img-wrapper img { max-height: 100%; max-width: 100%; box-shadow: 0 5px 15px rgba(0,0,0,0.1); transition: transform 0.3s; }
        .book-link:hover img { transform: scale(1.05); }
        .discount-badge { position: absolute; top: 15px; right: 15px; background: #ef4444; color: white; font-weight: 700; font-size: 0.85rem; padding: 5px 10px; border-radius: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); z-index: 10; pointer-events: none; }
        .price-original { text-decoration: line-through; color: var(--text-muted); font-size: 0.9rem; margin-right: 8px; }
        .price-current { font-size: 1.25rem; font-weight: bold; color: var(--primary); }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="hero-section">
    <div class="hero-overlay"></div>
    <div class="container hero-content text-center">
        <?php if($genre): ?>
            <h1 class="display-4 fw-bold mb-3"><?php echo htmlspecialchars($genre); ?> Books</h1>
            <p class="lead mb-4">Explore our curated collection of <?php echo strtolower(htmlspecialchars($genre)); ?> titles.</p>
        <?php else: ?>
            <h1 class="display-4 fw-bold mb-3">Unbeatable Deals</h1>
            <p class="lead mb-4">Save up to 50% on top programming and fiction titles!</p>
        <?php endif; ?>

        <div class="d-flex justify-content-center">
            <form action="index.php" method="GET" class="w-75">
                <?php if($genre): ?><input type="hidden" name="genre" value="<?php echo htmlspecialchars($genre); ?>"><?php endif; ?>
                <div class="input-group shadow-lg">
                    <input type="text" name="search" class="form-control form-control-lg border-0" 
                           placeholder="Search title or author..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary px-5 fw-bold">SEARCH</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h6 class="text-primary fw-bold text-uppercase small ls-1">
                <?php echo $genre ? "Category: " . htmlspecialchars($genre) : "Browse Collection"; ?>
            </h6>
            <h2 class="fw-bold mb-0">
                <?php 
                    if($search) echo 'Search Results for "' . htmlspecialchars($search) . '"';
                    else echo $genre ? "Best in " . htmlspecialchars($genre) : "All Books"; 
                ?>
            </h2>
        </div>
        
        <?php if (!empty($search) || !empty($genre)): ?>
            <a href="index.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">Clear Filters</a>
        <?php endif; ?>
    </div>

    <div class="row g-4 mb-5">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): 
                $orig = $row['original_price'];
                $curr = $row['price'];
                $discount = ($orig > 0) ? round((($orig - $curr) / $orig) * 100) : 0;
            ?>
                <div class="col-md-6 col-lg-4"> 
                    <div class="card book-card overflow-hidden border-0">
                        <div class="book-img-wrapper">
                            <?php if($discount > 0): ?>
                                <div class="discount-badge"><?php echo $discount; ?>% OFF</div>
                            <?php endif; ?>
                            <a href="buy.php?book_id=<?php echo $row['id']; ?>" class="book-link" title="View Details">
                                <img src="<?php echo htmlspecialchars($row['image']); ?>" class="img-fluid" alt="Book Cover"
                                     onerror="this.onerror=null;this.src='https://placehold.co/300x450?text=No+Cover';">
                            </a>
                        </div>
                        <div class="card-body d-flex flex-column p-4">
                            <a href="buy.php?book_id=<?php echo $row['id']; ?>" class="text-decoration-none text-reset">
                                <h4 class="card-title fw-bold text-truncate mb-1" title="<?php echo htmlspecialchars($row['title']); ?>">
                                    <?php echo htmlspecialchars($row['title']); ?>
                                </h4>
                            </a>
                            
                            <div class="mb-2">
                                <span class="badge bg-light text-secondary border"><?php echo htmlspecialchars($row['genre']); ?></span>
                            </div>

                            <p class="text-muted mb-3">by <?php echo htmlspecialchars($row['author']); ?></p>
                            
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <div>
                                    <?php if($orig > $curr): ?>
                                        <div class="d-flex align-items-baseline">
                                            <span class="price-original">₹<?php echo number_format($orig, 0); ?></span>
                                            <span class="price-current">₹<?php echo number_format($curr, 0); ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="price-current">₹<?php echo number_format($curr, 0); ?></span>
                                    <?php endif; ?>
                                </div>
                                <a href="buy.php?book_id=<?php echo $row['id']; ?>" class="btn btn-primary rounded-pill px-4">Buy Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <h4 class="text-muted">No books found in this category.</h4>
                <a href="index.php" class="btn btn-primary mt-3">View All Books</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php if($page <= 1){ echo 'disabled'; } ?>">
                <a class="page-link" href="<?php if($page > 1){ echo "?page=" . ($page - 1) . $query_params; } else { echo '#'; } ?>">Prev</a>
            </li>
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php if($page == $i) { echo 'active'; } ?>">
                    <a class="page-link" href="?page=<?php echo $i . $query_params; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php if($page >= $total_pages){ echo 'disabled'; } ?>">
                <a class="page-link" href="<?php if($page < $total_pages){ echo "?page=" . ($page + 1) . $query_params; } else { echo '#'; } ?>">Next</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
</body>
</html>