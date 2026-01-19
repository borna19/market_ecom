<footer class="bg-success text-light pt-5">
    <div class="container">
        <div class="row">

            <!-- BRAND INFO -->
            <div class="col-md-4 mb-4">
                <h5 class="fw-bold">🌱 Farmers Market</h5>
                <p class="small">
                    Fresh vegetables, fruits, and organic products directly from farmers
                    to your home.
                </p>
            </div>

            <!-- QUICK LINKS -->
            <div class="col-md-2 mb-4">
                <h6 class="text-uppercase fw-bold">Quick Links</h6>
                <ul class="list-unstyled">
                    <li><a href="/market_ecom/index.php" class="footer-link">Home</a></li>
                    <li><a href="/market_ecom/customer/shop.php" class="footer-link">Shop</a></li>
                    <li><a href="#" class="footer-link">About</a></li>
                    <li><a href="#" class="footer-link">Contact</a></li>
                </ul>
            </div>

            <!-- ACCOUNT -->
            <div class="col-md-3 mb-4">
                <h6 class="text-uppercase fw-bold">Account</h6>
                <ul class="list-unstyled">

                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <li><a href="/market_ecom/register.php" class="footer-link">Register</a></li>
                        <li><a href="/market_ecom/login.php" class="footer-link">Login</a></li>
                    <?php else: ?>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li><a href="/market_ecom/admin/dashboard.php" class="footer-link">Admin Dashboard</a></li>
                        <?php elseif ($_SESSION['role'] === 'vendor'): ?>
                            <li><a href="/market_ecom/vendor/dashboard.php" class="footer-link">Vendor Dashboard</a></li>
                        <?php else: ?>
                            <li><a href="/market_ecom/customer/dashboard.php" class="footer-link">My Account</a></li>
                        <?php endif; ?>
                        <li><a href="/market_ecom/logout.php" class="footer-link text-warning">Logout</a></li>
                    <?php endif; ?>

                </ul>
            </div>

            <!-- CONTACT -->
            <div class="col-md-3 mb-4">
                <h6 class="text-uppercase fw-bold">Contact</h6>
                <p class="small mb-1"><i class="fa fa-envelope"></i> support@farmersmarket.com</p>
                <p class="small mb-1"><i class="fa fa-phone"></i> +91 98765 43210</p>

                <div class="mt-3">
                    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                </div>
            </div>

        </div>

        <hr class="border-light opacity-50">

        <div class="text-center pb-3 small">
            © <?= date('Y'); ?> Farmers Market. All Rights Reserved.
        </div>
    </div>
</footer>
