✨ Features
Customer

Browse books by genre (Technology, Fiction, Business, Self-Help, Biography)
Search books by title or author
Paginated book listings (9 per page)
Detailed book pages with cover image, summary, and pricing
Discount badges with original vs. selling price
Add books to cart and checkout
Order history dashboard
Star ratings and reviews per book
Light / Dark mode toggle
Feedback submission

Admin

Secure admin-only dashboard
Add and delete books from inventory
View total revenue, book count, and customer count
View 5 most recent orders


🛠️ Tech Stack
LayerTechnologyBackendPHP 8DatabaseMySQLFrontendBootstrap 5, HTML5, CSS3, JavaScriptIconsFont Awesome 6FontsGoogle Fonts (Poppins)HostingInfinityFree / any PHP host

📁 Project Structure
luminabooks/
├── index.php              # Home page — book listing with search & filters
├── buy.php                # Individual book detail page + reviews
├── cart.php               # Shopping cart
├── checkout.php           # Order placement & confirmation
├── dashboard.php          # Customer order history
├── admin.php              # Admin panel (inventory + orders)
├── add_book.php           # Admin — add a book
├── delet_book.php         # Admin — delete a book
├── login.php              # User login
├── register.php           # User registration
├── logout.php             # Session logout
├── profile.php            # User profile view
├── feedback.php           # Customer feedback form
├── about.php              # About page
├── privacy.php            # Privacy policy
├── terms.php              # Terms of service
├── refund.php             # Refund policy
├── navbar.php             # Shared navbar (included on all pages)
├── footer.php             # Shared footer
├── db.php                 # Database connection
├── csrf.php               # CSRF token helper
├── styles.css             # Global stylesheet
└── .htaccess              # HTTPS redirect + security headers

🗄️ Database Schema
Run the following SQL to set up the required tables:
sqlCREATE DATABASE bookstore_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bookstore_db;

CREATE TABLE users (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100)  NOT NULL,
  email      VARCHAR(150)  NOT NULL UNIQUE,
  password   VARCHAR(255)  NOT NULL,
  role       ENUM('customer','admin') DEFAULT 'customer',
  phone      VARCHAR(20),
  address    TEXT,
  genre      VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE books (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  title          VARCHAR(200)  NOT NULL,
  author         VARCHAR(150)  NOT NULL,
  price          DECIMAL(10,2) NOT NULL,
  original_price DECIMAL(10,2) DEFAULT 0,
  genre          VARCHAR(50),
  image          TEXT,
  summary        TEXT,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  user_id      INT NOT NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  status       VARCHAR(50)   DEFAULT 'Completed',
  order_date   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE order_items (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  book_id  INT NOT NULL,
  price    DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id),
  FOREIGN KEY (book_id)  REFERENCES books(id)
);

CREATE TABLE reviews (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,
  book_id    INT NOT NULL,
  rating     TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  comment    TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (book_id) REFERENCES books(id),
  UNIQUE KEY unique_review (user_id, book_id)
);

CREATE TABLE feedback (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,
  feedback   TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);
To create an admin user:
sqlINSERT INTO users (name, email, password, role)
VALUES ('Admin', 'admin@example.com', '$2y$10$REPLACE_WITH_HASHED_PASSWORD', 'admin');
Generate a bcrypt hash from the command line:
bashphp -r "echo password_hash('YourPassword', PASSWORD_DEFAULT);"

⚙️ Installation
1. Clone the repository
bashgit clone https://github.com/yourusername/luminabooks.git
cd luminabooks
2. Set up the database

Create the database and run the SQL schema above in phpMyAdmin or MySQL CLI.

3. Configure environment variables
Do not hardcode credentials. Set these on your server:
VariableDescriptionDB_HOSTDatabase host (e.g. localhost)DB_USERDatabase usernameDB_PASSDatabase passwordDB_NAMEDatabase name
On a shared host (cPanel / InfinityFree), add these under PHP Configuration → Environment Variables. For local development you can set fallback values directly in db.php.
4. Upload files
Upload all project files to your public_html (or www) directory via FTP or your host's file manager.
5. Enable HTTPS
The included .htaccess automatically redirects all HTTP traffic to HTTPS. Make sure your host has an SSL certificate enabled.

🔐 Security Features

Passwords hashed with password_hash() (bcrypt)
CSRF protection on every form via signed tokens (csrf.php)
SQL injection prevention via prepared statements throughout
XSS prevention via htmlspecialchars() on all output
Rate limiting on login (5 failed attempts → 15-minute lockout)
Session fixation prevention via session_regenerate_id() on login
Role-based access control — admin routes are protected server-side
Generic error messages on login — does not reveal whether email exists
Security headers via .htaccess (X-Frame-Options, CSP, X-Content-Type-Options)
HTTPS enforcement via .htaccess redirect
Directory listing disabled via .htaccess


📸 Screenshots

Add screenshots of your homepage, book detail page, cart, and admin panel here.


🙋 Author
Mohd Salah
Full-Stack Web Developer
Built as a comprehensive demonstration of PHP + MySQL web development.

📄 License
This project is licensed under the MIT License.

🤝 Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.
