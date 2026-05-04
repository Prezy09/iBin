<?php
// app/Views/auth/login.php
// We don't use the main layout for login.
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>iBin &bull; Admin Login</title>
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
          <span class="hero-chip text-success bg-white px-3 py-2 rounded-pill fw-semibold small" id="heroChip">
            Checking live status...
          </span>
        </div>
        <p class="hero-lede text-white-soft mb-3">
          A Smart Waste Management System for Himamaylan City
        </p>

        <div class="hero-metrics row g-2">
          <div class="col-sm-6 col-xl-4">
            <div class="hero-card">
              <div class="label text-white-50">Connected bins</div>
              <div class="value display-5 fw-semibold" id="metricConnected">--</div>
              <div class="delta text-white-50" id="metricConnectedDelta">Awaiting telemetry</div>
              <div class="mini text-white-50" id="metricCoverage">Across network</div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-4">
            <div class="hero-card">
              <div class="label text-white-50">Average fill</div>
              <div class="value display-5 fw-semibold" id="metricAvgFill">--%</div>
              <div class="delta text-warning" id="metricNearFull">--</div>
              <div class="mini text-white-50" id="metricRefreshNote">Live telemetry</div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-4">
            <div class="hero-card">
              <div class="label text-white-50">Collections routed</div>
              <div class="value display-5 fw-semibold" id="metricCollections">--</div>
              <div class="delta text-white-50" id="metricCollectionsNote">Awaiting jobs</div>
              <div class="mini text-white-50">Based on bin thresholds</div>
            </div>
          </div>
        </div>

        <div class="activity-card mt-2">
          <div class="activity-head d-flex justify-content-between align-items-center mb-2">
            <strong>Live city moments</strong>
            <span class="badge bg-white text-success">Auto-sync every 60s</span>
          </div>
          <ul class="activity-list list-unstyled mb-0" id="activityList">
            <li class="activity-item text-white-50">Loading live activity...</li>
          </ul>
        </div>
      </div>
    </section>

    <section class="auth-pane">
      <div class="container px-3 px-md-4">
        <div class="auth-card glass-card login-card p-4 p-md-5">
          <div class="login-header text-center mb-2">
            <h2 class="fw-bold mb-1">Log in</h2>
            <p class="text-body-secondary mb-0">Access dashboards, routing tools, and alerts with your admin credentials.</p>
          </div>

          <div class="login-status row g-2 mb-2">
            <div class="col-6">
              <div class="status-tile">
                <span class="label text-body-secondary">System health</span>
                <span class="value text-success" id="statusHealth">Checking...</span>
                <small class="meta text-body-secondary" id="statusSource">Live telemetry</small>
              </div>
            </div>
            <div class="col-6">
              <div class="status-tile">
                <span class="label text-body-secondary">Last sync</span>
                <span class="value" id="statusSync">--</span>
                <small class="meta text-body-secondary" id="statusSyncMeta">Awaiting ping</small>
              </div>
            </div>
          </div>

          <?php if (!empty($registered)): ?>
            <div class="alert alert-success" role="alert">
              Account created successfully. You can now log in.
            </div>
          <?php endif; ?>

          <?php if (!empty($logout)): ?>
            <div class="alert alert-info" role="alert">
              You have been logged out.
            </div>
          <?php endif; ?>

          <?php if (!empty($timeout)): ?>
            <div class="alert alert-warning" role="alert">
              Your session expired after 10 minutes of inactivity. Please sign in again.
            </div>
          <?php endif; ?>

          <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
              <?php echo htmlspecialchars($error); ?>
            </div>
          <?php endif; ?>

          <form method="post" action="login" class="needs-validation" novalidate>
            <div class="floating-input mb-2">
              <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder=" " required>
              <label for="email">Work email</label>
              <span class="floating-icon"><i class="bi bi-envelope"></i></span>
              <div class="invalid-feedback">Enter a valid email.</div>
            </div>

            <div class="floating-input mb-1">
              <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder=" " required>
              <label for="password">Security passcode</label>
              <span class="floating-icon"><i class="bi bi-shield-lock"></i></span>
              <button class="ghost-button toggle-pass" type="button" tabindex="-1" aria-label="Show password">
                <i class="bi bi-eye"></i>
              </button>
              <div class="invalid-feedback">Enter your password.</div>
            </div>

            <div class="d-flex justify-content-between align-items-center small mb-2">
              <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                <label class="form-check-label" for="remember">Remember this device</label>
              </div>
              <a class="link-secondary text-decoration-none" href="#">Forgot password?</a>
            </div>

            <div class="d-grid">
              <button class="btn btn-success btn-lg login-btn" type="submit">Access dashboard</button>
            </div>

            <div class="login-divider my-2">
              <span>Need an account?</span>
            </div>

            <div class="d-grid">
              <a href="#" class="btn btn-outline-success btn-lg">Request admin access</a>
            </div>

            <div class="mobile-hero d-lg-none mt-4">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>Field snapshot</strong>
                <span class="badge bg-success-subtle text-success-emphasis">Live</span>
              </div>
              <div class="stat">
                <span>Active bins</span>
                <span id="mobileActive">--</span>
              </div>
              <div class="stat">
                <span>Average fill</span>
                <span id="mobileAvg">--%</span>
              </div>
              <div class="stat">
                <span>Next pickup</span>
                <span class="text-warning" id="mobileNext">Awaiting telemetry</span>
              </div>
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
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add('was-validated');
        }, false);
      });
    })();

    const toggleBtn = document.querySelector('.toggle-pass');
    if (toggleBtn) {
      toggleBtn.addEventListener('click', () => {
        const input = document.getElementById('password');
        const icon = toggleBtn.querySelector('i');
        if (!input || !icon) return;
        if (input.type === 'password') {
          input.type = 'text';
          icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
          input.type = 'password';
          icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
      });
    }

    document.getElementById('year').textContent = new Date().getFullYear();
  </script>
</body>
</html>
