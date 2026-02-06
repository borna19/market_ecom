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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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
            padding: 100px 0;
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }

        .section.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .navbar {
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-dark) !important;
        }

        .hero {
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://images.unsplash.com/photo-1464226184884-fa280b87c399?q=80&w=1470&auto=format&fit=crop') center/cover no-repeat fixed;
            color: #fff;
            padding: 180px 20px;
            text-align: center;
            position: relative;
        }

        .hero h1 {
            font-weight: 700;
            font-size: 4rem;
            text-shadow: 2px 2px 10px rgba(0,0,0,0.5);
            margin-bottom: 20px;
        }

        .hero p {
            font-size: 1.4rem;
            max-width: 700px;
            margin: 0 auto 30px;
            font-weight: 300;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 14px 35px;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(34, 197, 94, 0.3);
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: white;
            padding: 14px 35px;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s;
        }
        .btn-secondary:hover {
            background-color: #ea580c;
            border-color: #ea580c;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(249, 115, 22, 0.3);
        }

        .section-title {
            font-weight: 700;
            margin-bottom: 60px;
            text-align: center;
            position: relative;
            display: inline-block;
            left: 50%;
            transform: translateX(-50%);
        }

        .section-title::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background: var(--primary-color);
            margin: 15px auto 0;
            border-radius: 2px;
        }

        /* Why Choose Us - New Design */
        .video-container {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            width: 100%;
            padding-top: 56.25%; /* 16:9 Aspect Ratio */
            background: #000;
        }
        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
        .feature-list-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 30px;
        }
        .feature-icon-box {
            flex-shrink: 0;
            width: 60px;
            height: 60px;
            background: #dcfce7;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            color: var(--primary-color);
            font-size: 1.5rem;
            transition: all 0.3s;
        }
        .feature-list-item:hover .feature-icon-box {
            background: var(--primary-color);
            color: white;
            transform: rotate(10deg);
        }
        .feature-content h5 {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-dark);
        }
        .feature-content p {
            color: var(--text-light);
            font-size: 0.95rem;
            line-height: 1.6;
            margin: 0;
        }

        .how-it-works-card {
            background: transparent;
            border: none;
            text-align: center;
            position: relative;
        }

        .how-it-works-card .icon-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 2.5rem;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .how-it-works-card:hover .icon-circle {
            background: var(--primary-color);
            color: #fff;
            transform: scale(1.1) rotate(5deg);
        }

        .testimonial-card {
            background: #fff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            position: relative;
            margin-top: 30px;
        }

        .testimonial-card::before {
            content: '\f10d';
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-size: 2rem;
            color: var(--primary-color);
            opacity: 0.2;
            position: absolute;
            top: 20px;
            left: 20px;
        }

        .testimonial-card p {
            font-style: italic;
            color: var(--text-light);
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .testimonial-card strong {
            display: block;
            margin-top: 20px;
            color: var(--text-dark);
            font-weight: 600;
        }

        .cta {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
            color: #fff;
            padding: 100px 20px;
            text-align: center;
            border-radius: 30px;
            position: relative;
            overflow: hidden;
        }

        .cta::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .footer {
            background: #111827;
            color: #9ca3af;
            padding: 80px 0 30px;
        }
        .footer h5 { color: white; margin-bottom: 25px; }
        .footer a { color: #9ca3af; text-decoration: none; transition: color 0.2s; }
        .footer a:hover { color: var(--primary-color); }
        .social-icon {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 10px;
            transition: all 0.3s;
        }
        .social-icon:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-3px);
        }

        /* Modal Styling */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .modal-header {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
            color: white;
            border-bottom: none;
            padding: 25px 30px;
        }
        .modal-title {
            font-weight: 600;
            font-size: 1.4rem;
        }
        .modal-body {
            padding: 40px 30px;
            background-color: #fff;
        }
        .form-floating > .form-control {
            border-radius: 10px;
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
            top: 90px;
            right: 20px;
            z-index: 1050;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .toast-message {
            min-width: 300px;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 15px;
            opacity: 1;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
            transform: translateX(0);
            background: white;
            border-left: 5px solid;
        }
        .toast-message.fade-out {
            opacity: 0;
            transform: translateX(100%);
        }
        .toast-success { border-color: #22c55e; }
        .toast-success i { color: #22c55e; }
        .toast-danger { border-color: #ef4444; }
        .toast-danger i { color: #ef4444; }

        /* New Sections */
        .farmer-card {
            background: #fff;
            border: none;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            height: 100%;
        }
        .farmer-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .farmer-img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 20px;
        }
        .farmer-info { padding: 0; }

        .newsletter {
            background: #f0fdf4;
            padding: 80px 0;
        }
        .newsletter-form .form-control {
            border-radius: 50px 0 0 50px;
            padding: 15px 25px;
            border: 1px solid #bbf7d0;
        }
        .newsletter-form .btn {
            border-radius: 0 50px 50px 0;
            padding: 15px 30px;
        }

    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light fixed-top">
  <div class="container">
    <a class="navbar-brand" href="#"><i class="fa-solid fa-leaf text-success"></i> Farmers Market</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
        <li class="nav-item"><a class="nav-link" href="#how-it-works">How it Works</a></li>
        <li class="nav-item"><a class="nav-link" href="#farmers">Our Farmers</a></li>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <li class="nav-item ms-3"><a href="#" class="btn btn-outline-success px-4 rounded-pill" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a></li>
            <li class="nav-item ms-2"><a href="#" class="btn btn-success px-4 rounded-pill" data-bs-toggle="modal" data-bs-target="#registerModal">Register</a></li>
        <?php else: ?>
            <li class="nav-item ms-3"><a class="btn btn-primary px-4 rounded-pill" href="/market_ecom/logout.php">Logout</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Toast Container -->
<div class="toast-container">
    <?php if ($session_success): ?>
        <div class="toast-message toast-success" role="alert">
            <i class="fa-solid fa-circle-check fa-lg"></i>
            <div><strong>Success</strong><br><small><?= $session_success; ?></small></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($session_error): ?>
        <div class="toast-message toast-danger" role="alert">
            <i class="fa-solid fa-circle-exclamation fa-lg"></i>
            <div><strong>Error</strong><br><small><?= $session_error; ?></small></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
</div>

<!-- HERO -->
<section class="hero d-flex align-items-center">
  <div class="container">
    <h1 class="display-3">Fresh From Farmers to <br>Your Family Table</h1>
    <p class="lead">Experience the taste of real food. 100% Organic, locally sourced, and delivered with love directly from the farm.</p>
    <?php if (!isset($_SESSION['user_id'])): ?>
      <a href="#" class="btn btn-secondary btn-lg mt-4 shadow-lg" data-bs-toggle="modal" data-bs-target="#registerModal">
        Get Started Free <i class="fa-solid fa-arrow-right ms-2"></i>
      </a>
    <?php else: ?>
      <a href="<?php
        if ($_SESSION['role']=='admin') echo '/market_ecom/admin/admin_dashboard.php';
        elseif ($_SESSION['role']=='vendor') echo '/market_ecom/vendor/dashboard.php';
        else echo '/market_ecom/customer/customer_dashboard.php';
      ?>" class="btn btn-secondary btn-lg mt-4 shadow-lg">Go To Dashboard <i class="fa-solid fa-arrow-right ms-2"></i></a>
    <?php endif; ?>
  </div>
</section>

<!-- WHY CHOOSE US (Redesigned) -->
<section id="features" class="section bg-light">
    <div class="container">
        <h2 class="section-title">Why Choose Us</h2>
        <div class="row align-items-center g-5">
            <!-- Left Side: Video -->
            <div class="col-lg-6">
                <div class="video-container">
<iframe width="560" height="315" src="https://www.youtube.com/embed/1q8o6gRs-IA?si=V9QND9z9rogmIsIR" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>                </div>
            </div>
            <!-- Right Side: Features List -->
            <div class="col-lg-6">
                <div class="ps-lg-4">
                    <div class="feature-list-item">
                        <div class="feature-icon-box">
                            <i class="fa-solid fa-heart-pulse"></i>
                        </div>
                        <div class="feature-content">
                            <h5>Healthy Living</h5>
                            <p>We prioritize your health by ensuring all our produce is 100% organic and free from harmful chemicals. Eat fresh, live better.</p>
                        </div>
                    </div>
                    <div class="feature-list-item">
                        <div class="feature-icon-box">
                            <i class="fa-solid fa-handshake-angle"></i>
                        </div>
                        <div class="feature-content">
                            <h5>Support Local Farmers</h5>
                            <p>By shopping with us, you are directly supporting the livelihoods of local farmers and their families, ensuring fair trade.</p>
                        </div>
                    </div>
                    <div class="feature-list-item">
                        <div class="feature-icon-box">
                            <i class="fa-solid fa-truck-fast"></i>
                        </div>
                        <div class="feature-content">
                            <h5>Fast & Fresh Delivery</h5>
                            <p>From the farm to your doorstep in record time. We guarantee freshness with our efficient delivery network.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section id="how-it-works" class="section">
    <div class="container text-center">
        <h2 class="section-title">How It Works</h2>
        <div class="row g-5 justify-content-center">
            <div class="col-md-4">
                <div class="how-it-works-card">
                    <div class="icon-circle"><i class="fa-solid fa-user-plus"></i></div>
                    <h5 class="fw-bold">1. Register</h5>
                    <p class="text-muted">Create your free account in seconds.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="how-it-works-card">
                    <div class="icon-circle"><i class="fa-solid fa-basket-shopping"></i></div>
                    <h5 class="fw-bold">2. Shop</h5>
                    <p class="text-muted">Browse fresh products from local farmers.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="how-it-works-card">
                    <div class="icon-circle"><i class="fa-solid fa-house-chimney"></i></div>
                    <h5 class="fw-bold">3. Receive</h5>
                    <p class="text-muted">Get fresh delivery right to your door.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FEATURED PRODUCTS (Restored) -->
<section class="section bg-light">
    <div class="container">
        <h2 class="section-title">Fresh Arrivals</h2>
        <div class="row g-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6" class="card-img-top" style="height:200px; object-fit:cover;">
                    <div class="card-body text-center">
                        <h5 class="fw-bold">Red Apples</h5>
                        <p class="text-success fw-bold">₹120 / kg</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1598170845058-32b9d6a5da37" class="card-img-top" style="height:200px; object-fit:cover;">
                    <div class="card-body text-center">
                        <h5 class="fw-bold">Fresh Carrots</h5>
                        <p class="text-success fw-bold">₹60 / kg</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                    <img src="https://philnews.ph/wp-content/uploads/2019/06/TOMATO.jpg" class="card-img-top" style="height:200px; object-fit:cover;">
                    <div class="card-body text-center">
                        <h5 class="fw-bold">Organic Tomatoes</h5>
                        <p class="text-success fw-bold">₹40 / kg</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                    <img src="https://tse1.mm.bing.net/th/id/OIP.Iwpd-0C3ziKGXuYSTMATxgHaE6?rs=1&pid=ImgDetMain&o=7&rm=3" class="card-img-top" style="height:200px; object-fit:cover;">
                    <div class="card-body text-center">
                        <h5 class="fw-bold">Broccoli</h5>
                        <p class="text-success fw-bold">₹90 / kg</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center mt-5">
            <a href="#" class="btn btn-outline-success rounded-pill px-5" data-bs-toggle="modal" data-bs-target="#loginModal">View All Products</a>
        </div>
    </div>
</section>

<!-- OUR FARMERS (Restored) -->
<section id="farmers" class="section">
    <div class="container">
        <h2 class="section-title">Meet Our Farmers</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="farmer-card">
                    <img src="https://img.freepik.com/premium-photo/young-farmer-with-organic-vegetables-wooden-crates-he-is-going-deliver-fresh-vegetables-customers_44602-115.jpg?w=2000" class="farmer-img">
                    <div class="farmer-info">
                        <h5 class="fw-bold">Ramesh Kumar</h5>
                        <p class="text-muted small">Organic Vegetable Farmer</p>
                        <p class="text-muted">"I believe in growing food without chemicals for a better future."</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="farmer-card">
                    <img src="https://thumbs.dreamstime.com/b/positive-young-woman-farm-feeds-cows-silage-her-hands-hat-working-livestock-308095257.jpg" class="farmer-img">
                    <div class="farmer-info">
                        <h5 class="fw-bold">Sita Devi</h5>
                        <p class="text-muted small">Dairy Farmer</p>
                        <p class="text-muted">"Fresh milk and dairy products from happy cows."</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="farmer-card">
                    <img src="https://images.unsplash.com/photo-1535090467336-9501f96eef89" class="farmer-img">
                    <div class="farmer-info">
                        <h5 class="fw-bold">Abdul Khan</h5>
                        <p class="text-muted small">Fruit Orchard Owner</p>
                        <p class="text-muted">"Bringing the sweetest fruits directly from my orchard to you."</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- TESTIMONIALS -->
<section class="section bg-light">
    <div class="container">
        <h2 class="section-title">Happy Customers</h2>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="testimonial-card">
                    <p>"The quality of vegetables is unmatched. I love knowing exactly where my food comes from. Highly recommended!"</p>
                    <strong>- Priya Sharma</strong>
                    <div class="text-warning small"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="testimonial-card">
                    <p>"Finally, a platform that helps farmers get a fair price. The delivery is always on time and the produce is fresh."</p>
                    <strong>- Rahul Verma</strong>
                    <div class="text-warning small"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star-half-stroke"></i></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- NEWSLETTER -->
<section class="newsletter section">
    <div class="container text-center">
        <h2 class="fw-bold mb-3">Subscribe to our Newsletter</h2>
        <p class="text-muted mb-4">Get the latest updates on new harvests and special offers.</p>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <form class="d-flex newsletter-form">
                    <input type="email" class="form-control" placeholder="Enter your email">
                    <button class="btn btn-primary" type="button">Subscribe</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- FINAL CTA -->
<section class="section">
    <div class="container">
        <div class="cta">
            <h2 class="fw-bold display-5 mb-3" style="position:relative; z-index:1;">Join the Movement</h2>
            <p class="lead mb-4" style="position:relative; z-index:1;">Start eating healthy and supporting local farmers today.</p>
            <a href="#" class="btn btn-light btn-lg rounded-pill px-5 fw-bold" style="position:relative; z-index:1;" data-bs-toggle="modal" data-bs-target="#registerModal">
                Create Free Account
            </a>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5 class="fw-bold text-white"><i class="fa-solid fa-leaf text-success me-2"></i> Farmers Market</h5>
                <p>Connecting local farmers with communities for a healthier, sustainable future.</p>
                <div class="mt-4">
                    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="col-md-2 mb-4">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#">Home</a></li>
                    <li class="mb-2"><a href="#features">About Us</a></li>
                    <li class="mb-2"><a href="#farmers">Farmers</a></li>
                    <li class="mb-2"><a href="#">Contact</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h5>Support</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#">Help Center</a></li>
                    <li class="mb-2"><a href="#">Terms of Service</a></li>
                    <li class="mb-2"><a href="#">Privacy Policy</a></li>
                    <li class="mb-2"><a href="#">FAQs</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h5>Contact Us</h5>
                <ul class="list-unstyled text-muted">
                    <li class="mb-2"><i class="fa-solid fa-location-dot me-2"></i> 123 Green Street, Farmville</li>
                    <li class="mb-2"><i class="fa-solid fa-phone me-2"></i> +91 98765 43210</li>
                    <li class="mb-2"><i class="fa-solid fa-envelope me-2"></i> hello@farmersmarket.com</li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary my-4">
        <div class="text-center text-muted small">
            &copy; <?= date("Y") ?> Farmers Market. All rights reserved.
        </div>
    </div>
</footer>

<!-- LOGIN MODAL -->
<div class="modal fade" id="loginModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Welcome Back!</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="/market_ecom/includes/auth/login_action.php">
        <div class="modal-body">
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
          <button type="submit" name="login" class="btn btn-primary w-100 rounded-pill py-2">Login</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- REGISTER MODAL -->
<div class="modal fade" id="registerModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Join Us Today</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="/market_ecom/includes/auth/register_action.php">
        <div class="modal-body">
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
          <button type="submit" name="register" class="btn btn-primary w-100 rounded-pill py-2">Register</button>
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
