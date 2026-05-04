<?php
// register.php - User registration
session_start();
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/config/firebase.php';

$errors = [];
$name = $email = $role = '';
$requestSubmitted = false;
$isMasterAdmin = auth_is_master_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm = $_POST['confirm_password'] ?? '';
  $role = $_POST['role'] ?? 'User';

  if ($name === '') $errors[] = 'Name is required.';
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
  if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
  if ($password !== $confirm) $errors[] = 'Passwords do not match.';
  if (!in_array($role, ['Admin', 'Operator', 'User'])) $errors[] = 'Invalid role.';
  if (!firebase_is_ready()) $errors[] = 'Firebase is not configured on this server.';

  if (empty($errors)) {
    try {
      $existing = firebase_users_find_by_email($email);
      if ($existing) {
        $errors[] = 'Email is already registered.';
      } else {
        $pending = firebase_access_requests_find_by_email($email, ['Pending']);
        if ($pending) {
          $errors[] = 'You already have a pending access request.';
        } else {
          $hash = password_hash($password, PASSWORD_DEFAULT);
          firebase_access_request_create([
            'name' => $name,
            'email' => $email,
            'password_hash' => $hash,
            'role' => $role,
            'status' => 'Pending',
            'submitted_at' => gmdate('c'),
            'requested_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
          ]);
          $requestSubmitted = true;
          $name = $email = '';
          $role = 'User';
        }
      }
    } catch (Throwable $e) {
      $errors[] = 'Request failed: ' . $e->getMessage();
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Create Account &bull; iBin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="assets/css/theme.css" rel="stylesheet">
  <link href="assets/css/auth.css" rel="stylesheet">
</head>
<body class="auth-body">
  <div class="auth-shell">
    <section class="hero-pane text-white">
      <div class="hero-glow hero-glow-one"></div>
      <div class="hero-glow hero-glow-two"></div>
      <div class="hero-pattern hero-pattern-grid"></div>
      <div class="hero-content container px-4 px-xxl-5">
        <span class="hero-badge text-uppercase small mb-1">City Ops Release 2.4</span>
        <div class="hero-heading d-flex flex-column flex-lg-row align-items-lg-end gap-3 mb-2">
            <div class="d-flex align-items-center gap-3">
              <img src="assets/img/LOGO 1 SVG.svg" alt="Smart waste logo" class="hero-logo">
              <div>
                <p class="hero-eyebrow mb-1 text-white-50">Himamaylan City LGU</p>
                <h1 class="hero-title mb-0">iBin Smart Waste Management System</h1>
              </div>
            </div>
          <span class="hero-chip text-success bg-white px-3 py-2 rounded-pill fw-semibold small">
            Access portal
          </span>
        </div>
        <p class="hero-lede text-white-soft mb-3">
          Request secure access to the iBin Command Suite. The master admin will review and approve new accounts.
        </p>

        <div class="hero-metrics row g-2">
          <div class="col-sm-6 col-xl-4">
            <div class="hero-card">
              <div class="label text-white-50">Pending approvals</div>
              <div class="value display-6 fw-semibold">3</div>
              <div class="delta text-warning">Avg review: &lt; 1 day</div>
              <div class="mini text-white-50">Master admin only</div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-4">
            <div class="hero-card">
              <div class="label text-white-50">Admins online</div>
              <div class="value display-6 fw-semibold">2</div>
              <div class="delta delta-up">Live support</div>
              <div class="mini text-white-50">Weekdays 8AM - 6PM</div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-4">
            <div class="hero-card">
              <div class="label text-white-50">Security</div>
              <div class="value display-6 fw-semibold">2FA</div>
              <div class="delta text-white">Password policy enabled</div>
              <div class="mini text-white-50">Strong credentials required</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="auth-pane">
      <div class="container px-3 px-md-4">
        <div class="auth-card glass-card login-card p-4 p-md-5">
          <div class="login-header text-center mb-2">
            <h2 class="fw-bold mb-1">Request Access</h2>
            <p class="text-body-secondary mb-0">Submit your details to the master admin for approval.</p>
          </div>

          <?php if ($requestSubmitted): ?>
            <div class="alert alert-success" role="alert">
              Your request was sent to the master admin. You will be notified once it is approved.
            </div>
          <?php endif; ?>

          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
              <ul class="mb-0">
                <?php foreach ($errors as $e): ?>
                  <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <form method="post" class="needs-validation" novalidate>
            <div class="floating-input mb-2">
              <input type="text" class="form-control form-control-lg" id="name" name="name" placeholder=" " value="<?php echo htmlspecialchars($name); ?>" required>
              <label for="name">Full name</label>
              <span class="floating-icon"><i class="bi bi-person-circle"></i></span>
              <div class="invalid-feedback">Name is required.</div>
            </div>

            <div class="floating-input mb-2">
              <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder=" " value="<?php echo htmlspecialchars($email); ?>" required>
              <label for="email">Work email</label>
              <span class="floating-icon"><i class="bi bi-envelope"></i></span>
              <div class="invalid-feedback">A valid email is required.</div>
            </div>

            <div class="floating-input mb-2">
              <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder=" " required minlength="8">
              <label for="password">Create password</label>
              <span class="floating-icon"><i class="bi bi-lock"></i></span>
              <button class="ghost-button toggle-pass" type="button" tabindex="-1" aria-label="Show password" data-target="password">
                <i class="bi bi-eye"></i>
              </button>
              <div class="invalid-feedback">Password must be at least 8 characters.</div>
            </div>

            <div class="floating-input mb-2">
              <input type="password" class="form-control form-control-lg" id="confirm_password" name="confirm_password" placeholder=" " required>
              <label for="confirm_password">Confirm password</label>
              <span class="floating-icon"><i class="bi bi-shield-lock"></i></span>
              <button class="ghost-button toggle-pass" type="button" tabindex="-1" aria-label="Show password" data-target="confirm_password">
                <i class="bi bi-eye"></i>
              </button>
              <div class="invalid-feedback">Please confirm your password.</div>
            </div>

            <div class="mb-3">
              <label class="form-label" for="role">Role</label>
              <select class="form-select form-select-lg" id="role" name="role">
                <?php if ($isMasterAdmin): ?>
                  <option value="Admin" <?php echo $role === 'Admin' ? 'selected' : ''; ?>>Admin (approval required)</option>
                <?php endif; ?>
                <option value="Operator" <?php echo $role === 'Operator' ? 'selected' : ''; ?>>Operator (approval required)</option>
                <option value="User"  <?php echo $role === 'User' ? 'selected' : ''; ?>>User (approval required)</option>
              </select>
            </div>

            <div class="d-grid">
              <button class="btn btn-success btn-lg login-btn" type="submit">Send request</button>
            </div>

            <div class="login-divider my-2">
              <span>Need to sign in?</span>
            </div>

            <div class="d-grid">
              <a href="index.php" class="btn btn-outline-success btn-lg">Back to login</a>
            </div>
          </form>

          <p class="login-footer text-center text-body-secondary small mb-0 mt-2">
            &copy; <span id="year"></span> iBin Command Suite &middot; Privacy &middot; Terms
          </p>
        </div>
      </div>
    </section>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (() => {
      'use strict';
      const forms = document.querySelectorAll('.needs-validation');
      Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
          const passInput = document.getElementById('password');
          const confInput = document.getElementById('confirm_password');
          if (passInput && confInput) {
            const mismatch = passInput.value !== confInput.value;
            confInput.setCustomValidity(mismatch ? 'Passwords do not match.' : '');
          }
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add('was-validated');
        }, false);
      });
    })();

    const toggleBtns = document.querySelectorAll('.toggle-pass');
    toggleBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        const targetId = btn.getAttribute('data-target');
        const input = targetId ? document.getElementById(targetId) : null;
        const icon = btn.querySelector('i');
        if (!input || !icon) return;
        if (input.type === 'password') {
          input.type = 'text';
          icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
          input.type = 'password';
          icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
      });
    });

    document.getElementById('year').textContent = new Date().getFullYear();
  </script>
</body>
</html>
