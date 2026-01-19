<?php
include "includes/header.php";
?>

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
            <a href="register.php" class="btn btn-success btn-lg mt-3">
                Get Started
            </a>
        <?php else: ?>
            <a href="<?php
                if ($_SESSION['role'] === 'admin') echo 'admin/dashboard.php';
                elseif ($_SESSION['role'] === 'vendor') echo 'vendor/dashboard.php';
                else echo 'customer/dashboard.php';
            ?>" class="btn btn-success btn-lg mt-3">
                Go to Dashboard
            </a>
        <?php endif; ?>
    </div>
</section>

<!-- REGISTER MODAL -->
<div class="modal fade" id="registerModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Register</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form method="POST" action="auth/register_action.php">
        <div class="modal-body">

          <input type="text" name="name" class="form-control mb-3" placeholder="Full Name" required>

          <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>

          <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>

          <select name="role" class="form-select mb-3" required>
            <option value="">Register As</option>
            <option value="customer">Customer</option>
            <option value="vendor">Vendor</option>
            <option value="admin">Admin</option>
          </select>

        </div>

        <div class="modal-footer">
          <button type="submit" name="register" class="btn btn-success w-100">
            Create Account
          </button>
        </div>
      </form>

    </div>
  </div>
</div>
<!-- LOGIN MODAL -->
<div class="modal fade" id="loginModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Login</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form method="POST" action="auth/login_action.php">
        <div class="modal-body">

          <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>

          <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>

          <select name="role" class="form-select mb-3" required>
            <option value="">Login As</option>
            <option value="customer">Customer</option>
            <option value="vendor">Vendor</option>
            <option value="admin">Admin</option>
          </select>

        </div>

        <div class="modal-footer">
          <button type="submit" name="login" class="btn btn-success w-100">
            Login
          </button>
        </div>
      </form>

    </div>
  </div>
</div>





<!-- FEATURES SECTION -->
<section class="py-5">
    <div class="container">
        <div class="row text-center">

            <div class="col-md-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <i class="fa fa-leaf fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Fresh & Organic</h5>
                        <p class="card-text">
                            Directly sourced from farmers with guaranteed freshness.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <i class="fa fa-truck fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Fast Delivery</h5>
                        <p class="card-text">
                            Quick and reliable delivery at your doorstep.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <i class="fa fa-store fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Support Farmers</h5>
                        <p class="card-text">
                            Every purchase directly supports local farmers.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ROLE CTA SECTION -->
<section class="bg-success text-white py-5">
    <div class="container text-center">
        <h2 class="mb-4">Join Farmers Market</h2>

        <div class="row">

            <div class="col-md-4 mb-3">
                <h5>👤 Customer</h5>
                <p>Shop fresh products and track your orders easily.</p>
            </div>

            <div class="col-md-4 mb-3">
                <h5>🧑‍🌾 Vendor</h5>
                <p>Sell your products and manage earnings.</p>
            </div>

            <div class="col-md-4 mb-3">
                <h5>🛠 Admin</h5>
                <p>Manage users, products, and platform operations.</p>
            </div>

        </div>

        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="btn btn-light btn-lg mt-3">
                Register Now
            </a>
        <?php endif; ?>
    </div>
</section>
</div> <!-- content-wrapper -->


<?php include "includes/footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

