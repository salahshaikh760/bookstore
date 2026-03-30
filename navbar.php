<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* --- THEME VARIABLES --- */
    :root {
        /* LIGHT THEME (Default) */
        --bg-body: #f8fafc;
        --bg-nav: #ffffff;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --primary: #4f46e5;
        --primary-hover: #4338ca;
        --card-bg: #ffffff;
        --border-color: #e2e8f0;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        --pattern-color: rgba(99, 102, 241, 0.15); /* Light Indigo Dots */
    }

    [data-theme="dark"] {
        /* DARK THEME */
        --bg-body: #0f172a;
        --bg-nav: #1e293b;
        --text-main: #f1f5f9;
        --text-muted: #94a3b8;
        --primary: #6366f1;
        --primary-hover: #818cf8;
        --card-bg: #1e293b;
        --border-color: #334155;
        --shadow-sm: none;
        --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
        --pattern-color: rgba(255, 255, 255, 0.1); /* White Stars */
    }

    /* --- GLOBAL STYLES --- */
    body {
        font-family: 'Poppins', sans-serif;
        background-color: var(--bg-body);
        color: var(--text-main);
        transition: background-color 0.3s ease, color 0.3s ease;
        
        /* Grid Pattern Background */
        background-image: radial-gradient(var(--pattern-color) 1px, transparent 1px);
        background-size: 24px 24px;
        background-attachment: fixed;
    }

    /* Navbar Styling */
    .navbar {
        background-color: var(--bg-nav) !important;
        border-bottom: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        padding: 0.75rem 0;
    }
    
    .navbar-brand { font-weight: 700; color: var(--primary) !important; font-size: 1.5rem; }
    .nav-link { color: var(--text-muted) !important; font-weight: 500; transition: all 0.2s; }
    .nav-link:hover, .nav-link.active { color: var(--primary) !important; }
    
    /* Dropdown Styling */
    .dropdown-menu { background-color: var(--card-bg); border-color: var(--border-color); }
    .dropdown-item { color: var(--text-main); }
    .dropdown-item:hover { background-color: var(--bg-body); color: var(--primary); }

    /* Force Element Colors */
    h1, h2, h3, h4, h5, h6, .card-title { color: var(--text-main) !important; transition: color 0.3s; }
    .text-muted { color: var(--text-muted) !important; transition: color 0.3s; }
    .btn-primary { background-color: var(--primary); border-color: var(--primary); color: white !important; }
    .card { background-color: var(--card-bg); border: 1px solid var(--border-color); color: var(--text-main); }
</style>

<nav class="navbar navbar-expand-lg sticky-top">
  <div class="container">
    <a class="navbar-brand" href="index.php">
        <i class="fas fa-book-reader me-2"></i>Lumina<span class="text-secondary">Books</span>
    </a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
        <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navContent">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item me-2"><a class="nav-link" href="index.php">Home</a></li>
        
        <li class="nav-item dropdown me-2">
            <a class="nav-link dropdown-toggle" href="#" id="catDropdown" role="button" data-bs-toggle="dropdown">
                Categories
            </a>
            <ul class="dropdown-menu shadow">
                <li><a class="dropdown-item" href="index.php?genre=Technology"><i class="fas fa-laptop-code me-2"></i> Technology</a></li>
                <li><a class="dropdown-item" href="index.php?genre=Business"><i class="fas fa-chart-line me-2"></i> Business</a></li>
                <li><a class="dropdown-item" href="index.php?genre=Fiction"><i class="fas fa-dragon me-2"></i> Fiction</a></li>
                <li><a class="dropdown-item" href="index.php?genre=Self-Help"><i class="fas fa-spa me-2"></i> Self-Help</a></li>
                <li><a class="dropdown-item" href="index.php?genre=Biography"><i class="fas fa-user-tie me-2"></i> Biography</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="index.php">View All</a></li>
            </ul>
        </li>

        <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item me-2">
                <a class="nav-link" href="dashboard.php" title="My Orders">
                    <i class="fas fa-user-circle me-1"></i> My Orders
                </a>
            </li>
            
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <li class="nav-item me-2">
                <a class="nav-link text-warning fw-bold" href="admin.php" title="Store Stats">
                    <i class="fas fa-chart-line me-1"></i> Admin
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-item me-2">
                <a class="nav-link" href="cart.php">
                    <i class="fas fa-shopping-bag"></i> 
                    <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                        <span class="badge bg-danger rounded-circle p-1" style="font-size: 0.6rem; vertical-align: top;">
                            <?php echo count($_SESSION['cart']); ?>
                        </span>
                    <?php endif; ?>
                </a>
            </li>

            <li class="nav-item ms-3">
                <a href="logout.php" class="btn btn-outline-danger btn-sm px-4 rounded-pill">Log Out</a>
            </li>

        <?php else: ?>
            <li class="nav-item ms-3">
                <a href="login.php" class="btn btn-primary btn-sm px-4 rounded-pill shadow-sm">Sign In</a>
            </li>
        <?php endif; ?>

        <li class="nav-item ms-3 border-start ps-3" style="border-color: var(--border-color) !important;">
            <button class="btn nav-link p-0" id="theme-toggle" title="Toggle Theme"><i class="fas fa-moon"></i></button>
        </li>
      </ul>
    </div>
  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // THEME TOGGLE LOGIC
    const toggleBtn = document.getElementById('theme-toggle');
    const icon = toggleBtn.querySelector('i');
    
    function setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
    
    // Load saved theme
    const savedTheme = localStorage.getItem('theme') || 'light';
    setTheme(savedTheme);
    
    // Toggle on click
    toggleBtn.addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-theme');
        setTheme(current === 'dark' ? 'light' : 'dark');
    });
</script>