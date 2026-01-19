<?php
include "includes/header.php";
?>

<style>
    *{
        margin: 0;
        padding: 0;
    }
    </style>

<!-- HERO SECTION -->
<section class="bg-light py-5 text-center">
    <div class="container">
        <h1 class="display-5 fw-bold text-success">
            Fresh From Farm to Your Home 🌱
        </h1>
        <p class="lead mt-3">
            Buy fresh vegetables, fruits, and organic products directly from farmers.
        </p>

        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="#"
               class="btn btn-success btn-lg mt-3"
               data-bs-toggle="modal"
               data-bs-target="#registerModal">
                Get Started
            </a>
        <?php else: ?>
            <a href="<?php
                if ($_SESSION['role'] === 'admin') echo '/market_ecom/admin/admin_dashboard.php';
                elseif ($_SESSION['role'] === 'vendor') echo '/market_ecom/vendor/dashboard.php';
                else echo '/market_ecom/customer/dashboard.php';
            ?>" class="btn btn-success btn-lg mt-3">
                Go to Dashboard
            </a>
        <?php endif; ?>
    </div>
</section>

<!-- PLATFORM STATS -->
<section class="py-4 bg-white">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3"><h3 class="text-success">500+</h3><p>Happy Customers</p></div>
            <div class="col-md-3"><h3 class="text-success">120+</h3><p>Verified Farmers</p></div>
            <div class="col-md-3"><h3 class="text-success">300+</h3><p>Organic Products</p></div>
            <div class="col-md-3"><h3 class="text-success">24/7</h3><p>Support</p></div>
        </div>
    </div>
</section>

<!-- FEATURES SECTION -->
<section class="py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <i class="fa fa-leaf fa-3x text-success mb-3"></i>
                        <h5>Fresh & Organic</h5>
                        <p>Directly sourced from farmers with guaranteed freshness.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <i class="fa fa-truck fa-3x text-success mb-3"></i>
                        <h5>Fast Delivery</h5>
                        <p>Quick and reliable delivery at your doorstep.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <i class="fa fa-store fa-3x text-success mb-3"></i>
                        <h5>Support Farmers</h5>
                        <p>Every purchase directly supports local farmers.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- POPULAR PRODUCTS PREVIEW -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4">Popular Products</h2>
        <div class="row text-center">
            <div class="col-md-3 mb-3"><div class="card p-3">🥕 Fresh Carrots</div></div>
            <div class="col-md-3 mb-3"><div class="card p-3">🍅 Organic Tomatoes</div></div>
            <div class="col-md-3 mb-3"><div class="card p-3">🥬 Green Spinach</div></div>
            <div class="col-md-3 mb-3"><div class="card p-3">🍎 Red Apples</div></div>
        </div>
    </div>
</section>

<!-- TESTIMONIALS -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-4">What Our Customers Say</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="card p-3 shadow-sm">
                    <p>"Very fresh vegetables and fast delivery!"</p>
                    <strong>- Rahul</strong>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 shadow-sm">
                    <p>"I love supporting farmers directly."</p>
                    <strong>- Ayesha</strong>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 shadow-sm">
                    <p>"Best quality organic fruits online."</p>
                    <strong>- John</strong>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- NEWSLETTER -->
<section class="bg-success text-white py-5">
    <div class="container text-center">
        <h3>Subscribe for Offers & Updates</h3>
        <p>Get notified when fresh stock arrives.</p>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <input type="email" class="form-control mb-2" placeholder="Enter your email">
                <button class="btn btn-light">Subscribe</button>
            </div>
        </div>
    </div>
</section>

<!-- FINAL CTA -->
<section class="py-5 text-center bg-light">
    <div class="container">
        <h2>Ready to experience farm freshness?</h2>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="#"
               class="btn btn-success btn-lg mt-3"
               data-bs-toggle="modal"
               data-bs-target="#registerModal">
                Join Now
            </a>
        <?php endif; ?>
    </div>
</section>

<?php include "includes/footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
