<?php
require_once __DIR__ . '/includes/auth.php';
auth_require_login();
include_once __DIR__ . '/includes/layout.php';

render_header('About Us', ['active' => 'about', 'brand' => APP_BRAND_FULL]);
?>
<div class="about-page">
  <section class="about-hero dashboard-hero card p-4 mb-4">
    <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
      <div>
        <div class="pill mb-2">iBin • Smart Waste</div>
        <h1 class="fw-bold mb-2 about-title">Waste made smarter, cleaner, quieter.</h1>
        <p class="muted mb-0">
          An IoT-powered platform built by WVSU Himamaylan City Campus to give LGUs real-time bin visibility,
          faster dispatch, and data-driven sustainability decisions.
        </p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <span class="stat-chip">Live sensing & alerts</span>
        <span class="stat-chip">Route optimization</span>
        <span class="stat-chip">Segregation insights</span>
      </div>
    </div>
  </section>

  <div class="row g-3">
    <div class="col-12 col-lg-8">
      <div class="frost-card h-100">
        <h4 class="section-title">What we’re building</h4>
        <p class="muted mb-3">
          iBin blends hardware, maps, and analytics into a calm, reliable operations layer. Crews see which bins need attention,
          routes update in real time, and the city gets cleaner streets with less fuel and fewer overflows.
        </p>
        <div class="soft-divider"></div>
        <div class="row g-3">
          <div class="col-md-6">
            <h6 class="fw-semibold mb-1">Project snapshot</h6>
            <ul class="muted mb-0">
              <li><strong>Institution:</strong> West Visayas State University – Himamaylan City Campus</li>
              <li><strong>Platform:</strong> iBin Smart Waste</li>
              <li><strong>Focus:</strong> Live bin sensing, fill trends, segregation, and optimized routing</li>
              <li><strong>Region:</strong> Himamaylan City, Negros Occidental</li>
            </ul>
          </div>
          <div class="col-md-6">
            <h6 class="fw-semibold mb-1">Why it matters</h6>
            <ul class="muted mb-0">
              <li>Prevent overflow and litter through timely pickups.</li>
              <li>Reduce fuel and labor with data-backed dispatch.</li>
              <li>Support RA 9003 and local environmental targets.</li>
              <li>Guide future bin placement with usage analytics.</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-4">
      <div class="frost-card h-100">
        <h6 class="fw-semibold mb-2">System scope</h6>
        <ol class="muted mb-3">
          <li>Smart bins with fill-level, segregation, and GPS telemetry.</li>
          <li>Cloud data layer for alerts, storage, and reporting.</li>
          <li>Route optimization from the landfill using live status.</li>
          <li>Role-based web dashboards for admins, operators, users.</li>
        </ol>
        <div class="accent-badge mb-2">Built with care for city crews and citizens</div>
        <p class="muted mb-0">Leaflet maps, Bootstrap UI, and a lightweight analytics layer keep the experience fast, calm, and reliable.</p>
      </div>
    </div>
  </div>

  <div class="row g-3 mt-2">
    <div class="col-md-6">
      <div class="frost-card h-100">
        <h5 class="fw-bold mb-2">Mission</h5>
        <p class="muted mb-0">
          Deliver a quiet, always-on waste management assistant that keeps Himamaylan’s public spaces clean,
          supports frontline crews with live insights, and drives sustainable choices every day.
        </p>
      </div>
    </div>
    <div class="col-md-6">
      <div class="frost-card h-100">
        <h5 class="fw-bold mb-2">Vision</h5>
        <p class="muted mb-0">
          A city where waste never overflows, routes are effortless, and environmental data guides every decision—
          making sustainability feel as seamless as a well-designed app.
        </p>
      </div>
    </div>
  </div>

  <div class="frost-card mt-3">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-2 mb-3">
      <div>
        <h5 class="fw-bold mb-1">Authors</h5>
        <p class="muted mb-0">Meet the capstone team behind iBin.</p>
      </div>
      <span class="pill about-team-pill">Team iBin</span>
    </div>
    <div class="row g-3 justify-content-center">
      <?php
        $authors = [
          ['name' => 'Angel Joy Mombay', 'role' => 'Project Manager', 'photo' => 'assets/img/angel joy mombay.png'],
          ['name' => 'Christian Kenth Gonzaga', 'role' => 'Programmer', 'photo' => 'assets/img/christian kenth gonzaga.png'],
          ['name' => 'Kenneth Arnel Prestoza', 'role' => 'System Designer', 'photo' => 'assets/img/kenneth arnel prestoza.png'],
        ];
        foreach ($authors as $author):
          $initials = array_reduce(explode(' ', $author['name']), function($carry, $part){
            return $carry . strtoupper(substr($part, 0, 1));
          }, '');
          $photo = isset($author['photo']) ? trim($author['photo']) : '';
      ?>
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="person-card d-flex align-items-center gap-3">
          <?php if ($photo !== ''): ?>
            <img src="<?php echo htmlspecialchars($photo, ENT_QUOTES); ?>" alt="<?php echo htmlspecialchars($author['name']); ?> portrait" class="avatar-photo">
          <?php else: ?>
            <div class="avatar-placeholder"><?php echo htmlspecialchars($initials); ?></div>
          <?php endif; ?>
          <div>
            <div class="fw-semibold"><?php echo htmlspecialchars($author['name']); ?></div>
            <div class="muted small"><?php echo htmlspecialchars($author['role']); ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php render_footer(); ?>
