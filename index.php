<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Capture messages before outputting HTML
$session_success = $_SESSION['success'] ?? null;
$session_error = $_SESSION['error'] ?? null;

unset($_SESSION['success']);
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Farmers Market - Fresh, Local, Organic</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #22c55e; /* Green-500 */
            --primary-dark: #16a34a;  /* Green-600 */
            --secondary-color: #f97316; /* Orange-500 */
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --bg-light: #f9fafb;
        }

        body {
            font-family: 'Poppins', sans-serif;
            scroll-behavior: smooth;
            color: var(--text-dark);
            padding-top: 70px; /* Adjust for fixed navbar */
        }

        .section {
            padding: 80px 0;
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }

        .section.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .navbar {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .hero {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1542838132-92c53300491e') center/cover no-repeat;
            color: #fff;
            padding: 150px 20px;
            text-align: center;
        }

        .hero h1 {
            font-weight: 700;
            font-size: 3.5rem;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.6);
        }

        .hero p {
            font-size: 1.25rem;
            max-width: 600px;
            margin: 20px auto;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: white;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-secondary:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .section-title {
            font-weight: 700;
            margin-bottom: 50px;
            text-align: center;
        }

        .feature-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .feature-card i {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .how-it-works-card {
            background: transparent;
            border: none;
            text-align: center;
            position: relative;
        }

        .how-it-works-card .icon-circle {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
            transition: all 0.3s;
        }

        .how-it-works-card:hover .icon-circle {
            background: var(--primary-color);
            color: #fff;
            transform: scale(1.1);
        }

        .how-it-works-card:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 45px;
            right: -40%;
            width: 80%;
            height: 2px;
            background: repeating-linear-gradient(90deg, var(--primary-color), var(--primary-color) 6px, transparent 6px, transparent 12px);
            z-index: -1;
        }

        .testimonial-card {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            border-left: 5px solid var(--primary-color);
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .testimonial-card p {
            font-style: italic;
            color: var(--text-light);
        }

        .testimonial-card strong {
            display: block;
            margin-top: 15px;
            color: var(--text-dark);
        }

        .cta {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
            color: #fff;
            padding: 80px 20px;
            text-align: center;
            border-radius: 16px;
        }

        .footer {
            background: var(--text-dark);
            color: #e5e7eb;
            padding: 50px 0;
        }
        .footer a { color: var(--primary-color); text-decoration: none; }
        .footer a:hover { text-decoration: underline; }

        /* Modal Styling */
        .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .modal-header {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
            color: white;
            border-bottom: none;
            padding: 20px 30px;
        }
        .modal-title {
            font-weight: 600;
            font-size: 1.25rem;
        }
        .modal-body {
            padding: 30px;
            background-color: #fff;
        }
        .form-floating > .form-control {
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .form-floating > .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.1);
        }
        .modal-footer {
            border-top: none;
            padding: 0 30px 30px;
            background-color: #fff;
        }
        .btn-close-white {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: 80px; /* Below navbar */
            right: 20px;
            z-index: 1050; /* Above modals */
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .toast-message {
            min-width: 250px;
            max-width: 350px;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            opacity: 1;
            transition: opacity 0.3s ease-out;
        }
        .toast-message.fade-out {
            opacity: 0;
        }
        .toast-success {
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }
        .toast-danger {
            background-color: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }
        .toast-message .btn-close {
            margin-left: auto;
            font-size: 0.8rem;
        }

    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top">
  <div class="container">
    <a class="navbar-brand" href="#"><i class="fa-solid fa-leaf text-success"></i> Farmers Market</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <?php if (!isset($_SESSION['user_id'])): ?>
            <li class="nav-item"><a href="#" class="btn btn-outline-success me-2" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a></li>
            <li class="nav-item"><a href="#" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#registerModal">Register</a></li>
        <?php else: ?>
            <li class="nav-item"><a class="btn btn-primary" href="/market_ecom/logout.php">Logout</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Toast Container for Session Messages -->
<div class="toast-container">
    <?php if ($session_success): ?>
        <div class="toast-message toast-success" role="alert">
            <i class="fa fa-circle-check"></i> <?= $session_success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($session_error): ?>
        <div class="toast-message toast-danger" role="alert">
            <i class="fa fa-triangle-exclamation"></i> <?= $session_error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
</div>

<!-- HERO -->
<section class="hero">
  <div class="container">
    <h1 class="display-4">Fresh From Farmers to Your Family Table</h1>
    <p class="lead">Healthy food. Honest prices. Direct from the farm.</p>
    <?php if (!isset($_SESSION['user_id'])): ?>
      <a href="#" class="btn btn-secondary btn-lg mt-4" data-bs-toggle="modal" data-bs-target="#registerModal">
        Get Started Free
      </a>
    <?php else: ?>
      <a href="<?php
        if ($_SESSION['role']=='admin') echo '/market_ecom/admin/admin_dashboard.php';
        elseif ($_SESSION['role']=='vendor') echo '/market_ecom/vendor/dashboard.php';
        else echo '/market_ecom/customer/dashboard.php';
      ?>" class="btn btn-secondary btn-lg mt-4">Go To Dashboard</a>
    <?php endif; ?>
  </div>
</section>

<!-- WHY CHOOSE US -->
<section class="section bg-light">
    <div class="container">
        <h2 class="section-title">Why People Love Farmers Market</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <i class="fa-solid fa-heart-pulse"></i>
                    <h5 class="fw-bold mt-3">Healthy Living</h5>
                    <p class="text-light">Eat fresh, chemical-free food every day for a healthier lifestyle.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <i class="fa-solid fa-handshake-angle"></i>
                    <h5 class="fw-bold mt-3">Support Farmers</h5>
                    <p class="text-light">Your purchase directly supports local farmers and their families.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <i class="fa-solid fa-truck-fast"></i>
                    <h5 class="fw-bold mt-3">Easy & Fast</h5>
                    <p class="text-light">Order in minutes and get fresh produce delivered to your doorstep.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="section">
    <div class="container text-center">
        <h2 class="section-title">How It Works</h2>
        <div class="row g-4 align-items-center">
            <div class="col-md-4">
                <div class="how-it-works-card">
                    <div class="icon-circle"><i class="fa-solid fa-user-plus"></i></div>
                    <h5 class="fw-bold">Register</h5>
                    <p class="text-light">Create your free account in just a few simple steps.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="how-it-works-card">
                    <div class="icon-circle"><i class="fa-solid fa-basket-shopping"></i></div>
                    <h5 class="fw-bold">Choose Products</h5>
                    <p class="text-light">Browse a wide variety of fresh, locally-sourced products.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="how-it-works-card">
                    <div class="icon-circle"><i class="fa-solid fa-house-chimney"></i></div>
                    <h5 class="fw-bold">Enjoy Delivery</h5>
                    <p class="text-light">Get your order delivered fresh and fast, right to your door.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- TESTIMONIALS -->
<section class="section bg-light">
    <div class="container">
        <h2 class="section-title">What People Are Saying</h2>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="testimonial-card">
                    <p>"The best platform for fresh vegetables. I use it every week and the quality is always amazing!"</p>
                    <strong>- Rahim, Customer</strong>
                </div>
            </div>
            <div class="col-md-6">
                <div class="testimonial-card">
                    <p>"Now I can sell my produce directly without any middlemen. This is a great platform for farmers."</p>
                    <strong>- Abdul, Farmer</strong>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FINAL CTA -->
<section class="section">
    <div class="container">
        <div class="cta">
            <h2 class="fw-bold">Join Hundreds of Happy Users Today</h2>
            <p>Create your free account and start ordering fresh, healthy food.</p>
            <a href="#" class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#registerModal">
                Create Free Account
            </a>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3">
                <h5 class="fw-bold"><i class="fa-solid fa-leaf text-success"></i> Farmers Market</h5>
                <p>Connecting local farmers with communities.</p>
            </div>
            <div class="col-md-2 mb-3">
                <h5 class="fw-bold">Links</h5>
                <ul class="list-unstyled">
                    <li><a href="#">Home</a></li>
                    <li><a href="#">About</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-3">
                <h5 class="fw-bold">Follow Us</h5>
                <a href="#" class="me-2"><i class="fab fa-facebook fa-2x"></i></a>
                <a href="#" class="me-2"><i class="fab fa-twitter fa-2x"></i></a>
                <a href="#" class="me-2"><i class="fab fa-instagram fa-2x"></i></a>
            </div>
            <div class="col-md-3 mb-3">
                <h5 class="fw-bold">Legal</h5>
                <ul class="list-unstyled">
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                </ul>
            </div>
        </div>
        <hr>
        <div class="text-center pt-3">
            &copy; <?= date("Y") ?> Farmers Market | Built with ❤️
        </div>
    </div>
</footer>

<!-- LOGIN MODAL -->
<div class="modal fade" id="loginModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Login to Your Account</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="/market_ecom/includes/auth/login_action.php">
        <div class="modal-body p-4">
          <div class="form-floating mb-3">
            <input type="email" name="email" class="form-control" id="loginEmail" placeholder="Email" required>
            <label for="loginEmail">Email Address</label>
          </div>
          <div class="form-floating mb-3">
            <input type="password" name="password" class="form-control" id="loginPassword" placeholder="Password" required>
            <label for="loginPassword">Password</label>
          </div>
          <div class="form-floating">
            <select name="role" class="form-select" id="loginRole" required>
              <option value="" selected disabled>Login As</option>
              <option value="customer">Customer</option>
              <option value="vendor">Vendor</option>
              <option value="admin">Admin</option>
            </select>
            <label for="loginRole">Login As</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="login" class="btn btn-success w-100">Login</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- REGISTER MODAL -->
<div class="modal fade" id="registerModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Create a New Account</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="/market_ecom/includes/auth/register_action.php">
        <div class="modal-body p-4">
          <div class="form-floating mb-3">
            <input type="text" name="name" class="form-control" id="regName" placeholder="Full Name" required>
            <label for="regName">Full Name</label>
          </div>
          <div class="form-floating mb-3">
            <input type="email" name="email" class="form-control" id="regEmail" placeholder="Email" required>
            <label for="regEmail">Email Address</label>
          </div>
          <div class="form-floating mb-3">
            <input type="password" name="password" class="form-control" id="regPassword" placeholder="Password" required>
            <label for="regPassword">Password</label>
          </div>
          <div class="form-floating">
            <select name="role" class="form-select" id="regRole" required>
              <option value="" selected disabled>Register As</option>
              <option value="customer">Customer</option>
              <option value="vendor">Vendor</option>
            </select>
            <label for="regRole">Register As</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="register" class="btn btn-success w-100">Register</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Section animations
        const sections = document.querySelectorAll('.section');
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        sections.forEach(section => {
            observer.observe(section);
        });

        // Toast auto-dismissal
        const toasts = document.querySelectorAll('.toast-message');
        toasts.forEach(toast => {
            setTimeout(() => {
                toast.classList.add('fade-out');
                toast.addEventListener('transitionend', () => toast.remove());
            }, 5000); // Dismiss after 5 seconds
        });
    });
</script>
</body>
</html>
