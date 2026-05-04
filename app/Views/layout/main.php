<?php
// app/Views/layout/main.php
require_once __DIR__ . '/../../../includes/auth.php';

if (!defined('APP_BRAND_SHORT')) {
  define('APP_BRAND_SHORT', 'iBin');
}

if (!defined('APP_BRAND_FULL')) {
  define('APP_BRAND_FULL', 'iBin - IoT - Based Smart Waste management for Himamaylan City');
}

$title = $title ?? 'iBin Dashboard';
$active = $active ?? '';
$brand = $brand ?? APP_BRAND_FULL;
$theme = $theme ?? 'light';
$extra_head = $extra_head ?? '';

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('\\', '/', dirname($scriptName));
if ($scriptDir === '/' || $scriptDir === '\\' || $scriptDir === '.') {
  $scriptDir = '';
}
$basePath = $scriptDir;
$basePath = rtrim(str_replace('\\', '/', $basePath), '/');
$baseHref = $basePath === '' ? '/' : $basePath . '/';
$assetPrefix = $basePath === '' ? '/assets' : $basePath . '/assets';
$assetVersion = file_exists(__DIR__ . '/../../../assets/js/app.js') ? filemtime(__DIR__ . '/../../../assets/js/app.js') : time();

$title_attr = htmlspecialchars($title, ENT_QUOTES);
$theme_attr = htmlspecialchars($theme, ENT_QUOTES);
$basiKey = getenv('BASI_API_KEY') ?? getenv('BASIC_API_KEY') ?? 'eyJvcmciOiI1YjNjZTM1OTc4NTExMTAwMDFjZjYyNDgiLCJpZCI6IjFhMzQ1OWY2YWU0OTQwZTk5M2JkNzhiNWUxZWI0NzcxIiwiaCI6Im11cm11cjY0In0=';
$mapConfigScript = '<script>window.BASIC_API_KEY = ' . json_encode($basiKey, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) . ';window.BASI_API_KEY = window.BASIC_API_KEY;window.IBIN_LANDFILL = {lat:10.090419084954268,lng:122.86834693751759};</script>';

$currentRole = $_SESSION['user_role'] ?? 'User';
$roleIcon = match ($currentRole) {
  'Admin' => 'bi-shield-lock',
  'Operator' => 'bi-truck',
  default => 'bi-person-badge'
};
$roleJson = json_encode($currentRole, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
$isMasterAdmin = auth_is_master_admin();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= $theme_attr ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $title_attr ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link href="<?= $assetPrefix ?>/css/theme.css?v=<?= $assetVersion ?>" rel="stylesheet">
  <link href="<?= $assetPrefix ?>/css/pages.css?v=<?= $assetVersion ?>" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
  <script defer src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
  <?= $mapConfigScript ?>
  <script>window.IBIN_USER_ROLE = <?= $roleJson ?>;</script>
  <?= $extra_head ?>
</head>
<body class="bg-body-tertiary" data-user-role="<?= htmlspecialchars($currentRole, ENT_QUOTES) ?>">
  <div class="d-flex app-shell">
    <!-- Sidebar -->
    <aside class="sidebar border-end bg-white">
      <div class="sidebar-brand border-bottom">
        <img src="<?= $assetPrefix ?>/img/LOGO 1 SVG.svg" alt="iBin leaf logo" class="sidebar-brand-logo">
        <img src="<?= $assetPrefix ?>/img/LOGO 2 BLACK.svg" alt="iBin wordmark" class="sidebar-brand-logo single">
      </div>
      <div class="sidebar-content d-flex flex-column">
        <nav class="nav flex-column app-nav flex-grow-1">
          <a class="nav-link <?= $active === 'dashboard' ? 'active' : '' ?>" href="<?= $baseHref ?>dashboard">
            <i class="fa-solid fa-gauge-high nav-icon"></i>
            <span>Dashboard</span>
          </a>
          <?php if ($currentRole === 'Admin' || $currentRole === 'Operator'): ?>
            <a class="nav-link <?= $active === 'bins' ? 'active' : '' ?>" href="<?= $baseHref ?>bins">
              <i class="fa-solid fa-trash-can nav-icon"></i>
              <span>Bins</span>
            </a>
            <a class="nav-link <?= $active === 'collection' ? 'active' : '' ?>" href="<?= $baseHref ?>collection">
              <i class="fa-solid fa-truck nav-icon"></i>
              <span>Collection</span>
            </a>
          <?php endif; ?>
          <?php if ($currentRole === 'Admin'): ?>
            <a class="nav-link <?= $active === 'analytics' ? 'active' : '' ?>" href="<?= $baseHref ?>analytics.php">
              <i class="fa-solid fa-chart-line nav-icon"></i>
              <span>Analytics</span>
            </a>
            <a class="nav-link <?= $active === 'reports' ? 'active' : '' ?>" href="<?= $baseHref ?>reports.php">
              <i class="fa-solid fa-file-lines nav-icon"></i>
              <span>Reports</span>
            </a>
            <a class="nav-link <?= $active === 'settings' ? 'active' : '' ?>" href="<?= $baseHref ?>settings.php">
              <i class="fa-solid fa-gear nav-icon"></i>
              <span>Settings</span>
            </a>
          <?php endif; ?>
          <?php if ($isMasterAdmin): ?>
            <a class="nav-link <?= $active === 'manage_admins' ? 'active' : '' ?>" href="<?= $baseHref ?>manage_admins.php">
              <i class="fa-solid fa-user-shield nav-icon"></i>
              <span>Manage Accounts</span>
            </a>
          <?php endif; ?>
          <a class="nav-link <?= $active === 'about' ? 'active' : '' ?>" href="<?= $baseHref ?>about.php">
            <i class="fa-solid fa-circle-info nav-icon"></i>
            <span>About Us</span>
          </a>
        </nav>
        <div class="sidebar-footer p-3 border-top">
          <a class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2" href="<?= $baseHref ?>logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
          </a>
        </div>
      </div>
    </aside>
    <div class="sidebar-overlay d-lg-none" id="sidebarOverlay"></div>
    <!-- Main content -->
    <main class="flex-grow-1">
      <header class="app-topbar d-flex align-items-center justify-content-between border-bottom bg-white sticky-top">
        <div class="d-flex align-items-center gap-2">
          <button class="btn btn-outline-secondary btn-sm d-lg-none me-2" id="sidebarToggle" type="button" aria-label="Toggle navigation">
            <i class="bi bi-list"></i>
          </button>
          <h3 class="m-0 fw-bold"><?= $title_attr ?></h3>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span id="clock" class="text-secondary small"></span>
          <span class="role-pill"><i class="bi <?= $roleIcon ?> me-1"></i><?= htmlspecialchars($currentRole) ?></span>
          <div class="notification-wrapper">
            <button class="btn btn-outline-secondary btn-sm position-relative" id="notificationToggle" type="button" aria-label="View notifications">
              <i class="bi bi-bell"></i>
              <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle d-none" id="notificationCount">0</span>
            </button>
            <div class="notification-menu card shadow-sm" id="notificationMenu" hidden>
              <div class="notification-menu-header d-flex justify-content-between align-items-center">
                <strong>Activity</strong>
                <div class="d-flex align-items-center gap-2">
                  <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" id="notificationClearBtn">Clear</button>
                  <small id="notificationUpdated" class="text-secondary"></small>
                </div>
              </div>
              <div class="notification-list" id="notificationList"></div>
              <div class="notification-empty text-secondary small text-center py-3" id="notificationEmpty">You're all caught up!</div>
            </div>
          </div>
        </div>
      </header>
      <div class="container-fluid p-3">
        <?= $content ?>
      </div> <!-- /.container-fluid -->
    </main>
  </div> <!-- /.d-flex -->
  <div class="global-toast" id="globalToast" role="status" aria-live="polite"></div>
  <div class="modal fade ibin-modal" id="ibinConfirmModal" tabindex="-1" aria-labelledby="ibinConfirmTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <p class="modal-eyebrow mb-1 text-uppercase small">Confirm Action</p>
            <h5 class="modal-title mb-0" id="ibinConfirmTitle">Are you sure?</h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-0 text-secondary" id="ibinConfirmMessage">This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-success" id="ibinConfirmOk">Confirm</button>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script src="<?= $assetPrefix ?>/js/app.js?v=<?= $assetVersion ?>" type="module"></script>
  <script>
    // Copy the inline scripts from layout.php here
    (() => {
      const body = document.body;
      const sidebarToggle = document.getElementById('sidebarToggle');
      const sidebarOverlay = document.getElementById('sidebarOverlay');
      const sidebarNavLinks = document.querySelectorAll('.sidebar .nav-link');
      const DESKTOP_MEDIA = window.matchMedia('(min-width: 992px)');
      const notifToggle = document.getElementById('notificationToggle');
      const notifMenu = document.getElementById('notificationMenu');
      const notifList = document.getElementById('notificationList');
      const notifCount = document.getElementById('notificationCount');
      const notifEmpty = document.getElementById('notificationEmpty');
      const notifUpdated = document.getElementById('notificationUpdated');
      const notifClear = document.getElementById('notificationClearBtn');
      const toastEl = document.getElementById('globalToast');
      let activityClockTimer = null;

      function startActivityClock(){
        if (!notifUpdated) return;
        if (activityClockTimer) {
          clearInterval(activityClockTimer);
        }
        const render = () => {
          const now = new Date();
          notifUpdated.textContent = now.toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
            timeZone: 'Asia/Manila'
          });
        };
        render();
        activityClockTimer = setInterval(render, 1000);
      }
      startActivityClock();
      const USER_ROLE = window.IBIN_USER_ROLE || 'User';
      let notificationTimer = null;
      let lastDeliveryCount = 0;
      let lastPickupCount = 0;
      let lastRouteConfirmationCount = 0;
      let notificationsInitialized = false;
      let lastNotificationData = null;
      const NOTIF_ACK_KEY = 'ibin_notif_ack_signature';
      let notificationAckSignature = loadNotificationAckSignature();

      function loadNotificationAckSignature(){
        if (typeof localStorage === 'undefined') return null;
        try{
          return localStorage.getItem(NOTIF_ACK_KEY);
        }catch(err){
          console.warn('Unable to read notification ack', err);
          return null;
        }
      }

      function persistNotificationAckSignature(signature){
        if (typeof localStorage === 'undefined') return;
        try{
          if (signature) {
            localStorage.setItem(NOTIF_ACK_KEY, signature);
          } else {
            localStorage.removeItem(NOTIF_ACK_KEY);
          }
        }catch(err){
          console.warn('Unable to persist notification ack', err);
        }
      }

      function setSidebarState(open){
        body.classList.toggle('sidebar-open', Boolean(open));
        if (sidebarToggle) {
          sidebarToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
        if (!open && sidebarOverlay) {
          sidebarOverlay.setAttribute('aria-hidden', 'true');
        } else if (sidebarOverlay) {
          sidebarOverlay.removeAttribute('aria-hidden');
        }
      }

      function toggleSidebar(force){
        const shouldOpen = typeof force === 'boolean' ? force : !body.classList.contains('sidebar-open');
        setSidebarState(shouldOpen);
      }

      sidebarToggle?.addEventListener('click', () => toggleSidebar());
      sidebarOverlay?.addEventListener('click', () => toggleSidebar(false));
      sidebarNavLinks.forEach(link => {
        link.addEventListener('click', () => {
          if (!DESKTOP_MEDIA.matches) {
            setSidebarState(false);
          }
        });
      });
      const handleViewportChange = () => {
        if (DESKTOP_MEDIA.matches) {
          body.classList.remove('sidebar-open');
          sidebarToggle?.setAttribute('aria-expanded', 'false');
        } else {
          setSidebarState(false);
        }
      };
      if (typeof DESKTOP_MEDIA.addEventListener === 'function') {
        DESKTOP_MEDIA.addEventListener('change', handleViewportChange);
      } else if (typeof DESKTOP_MEDIA.addListener === 'function') {
        DESKTOP_MEDIA.addListener(handleViewportChange);
      }
      handleViewportChange();

      notifToggle?.addEventListener('click', (event) => {
        event.stopPropagation();
        if (notifMenu?.hasAttribute('hidden')) {
          notifMenu.removeAttribute('hidden');
        } else {
          notifMenu?.setAttribute('hidden', 'hidden');
        }
      });

      document.addEventListener('click', (event) => {
        if (!notifMenu || notifMenu.hasAttribute('hidden')) return;
        if (event.target.closest('#notificationToggle') || event.target.closest('#notificationMenu')) {
          return;
        }
        notifMenu.setAttribute('hidden', 'hidden');
      });

      const escapeHTML = (value) => String(value ?? '').replace(/[&<>"']/g, ch => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
      }[ch] || ch));

      function buildNotificationSignature(data){
        if (!data) return '';
        const counts = data.counts || {};
        const stops = Array.isArray(data?.stops) ? data.stops.map(stop => ({
          id: stop?.bin_id || stop?.id || '',
          status: stop?.status || '',
          updated_at: stop?.updated_at || ''
        })) : [];
        const alerts = Array.isArray(data?.alerts) ? data.alerts.map(alert => ({
          type: alert?.type || '',
          title: alert?.title || '',
          detail: alert?.detail || ''
        })) : [];
        return JSON.stringify({ counts, stops, alerts });
      }

      function applyNotificationData(data){
        lastNotificationData = data || {};
        const signature = buildNotificationSignature(data);
        const ackMatched = Boolean(signature && notificationAckSignature && signature === notificationAckSignature);
        if (!ackMatched && signature && notificationAckSignature && signature !== notificationAckSignature) {
          notificationAckSignature = null;
          persistNotificationAckSignature(null);
        }
        const alerts = ackMatched ? [] : (Array.isArray(data?.alerts) ? data.alerts : []);
        if (notifCount) {
          notifCount.textContent = alerts.length;
          notifCount.classList.toggle('d-none', alerts.length === 0);
        }
        if (notifUpdated) {
          const generated = data?.generated_at ? new Date(data.generated_at) : new Date();
          notifUpdated.textContent = generated.toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
            timeZone: 'Asia/Manila'
          });
        }
        if (notifList) {
          notifList.innerHTML = alerts.map(alert => `
            <div class="notification-item ${alert.severity || 'info'}">
              <div class="notification-icon">
                <i class="bi ${alert.icon || 'bi-info-circle'}"></i>
              </div>
              <div>
                <div class="fw-semibold">${escapeHTML(alert.title || alert.message)}</div>
                ${alert.detail ? `<div class="small text-secondary">${escapeHTML(alert.detail)}</div>` : ''}
              </div>
            </div>
          `).join('');
        }
        if (notifEmpty) {
          notifEmpty.hidden = alerts.length !== 0;
        }
        const displayCounts = ackMatched ? { pickups: 0, delivered: 0, routes_confirmed: 0 } : (data?.counts ?? {});
        const pickupCount = displayCounts.pickups ?? 0;
        const deliveredCount = displayCounts.delivered ?? 0;
        const confirmedRoutes = displayCounts.routes_confirmed ?? 0;
        const allowToasts = notificationsInitialized;
        if (allowToasts && pickupCount > lastPickupCount && USER_ROLE === 'Admin') {
          showToast(`Pickup alert: ${pickupCount} bin(s) awaiting collection.`, 'warning');
        }
        if (allowToasts && deliveredCount > lastDeliveryCount && (USER_ROLE === 'Admin' || USER_ROLE === 'Operator')) {
          showToast(`Delivery update: ${deliveredCount} bin(s) delivered.`, 'success');
        }
        if (allowToasts && confirmedRoutes > lastRouteConfirmationCount && USER_ROLE === 'Admin') {
          showToast('Pickup route confirmed by operators.', 'success');
        }
        if (!notificationsInitialized) {
          notificationsInitialized = true;
        }
        lastPickupCount = pickupCount;
        lastDeliveryCount = deliveredCount;
        lastRouteConfirmationCount = confirmedRoutes;
      }

      function clearNotifications(){
        const ackSignature = buildNotificationSignature(lastNotificationData);
        if (ackSignature) {
          notificationAckSignature = ackSignature;
          persistNotificationAckSignature(ackSignature);
        }
        lastNotificationData = lastNotificationData || {};
        lastNotificationData.alerts = [];
        if (lastNotificationData.counts) {
          lastNotificationData.counts.pickups = 0;
          lastNotificationData.counts.delivered = 0;
          lastNotificationData.counts.routes_confirmed = 0;
        }
        if (notifList) {
          notifList.innerHTML = '';
        }
        if (notifCount) {
          notifCount.textContent = '0';
          notifCount.classList.add('d-none');
        }
        if (notifEmpty) {
          notifEmpty.hidden = false;
        }
        if (notifUpdated) {
          notifUpdated.textContent = 'Cleared';
        }
        lastPickupCount = 0;
        lastDeliveryCount = 0;
        lastRouteConfirmationCount = 0;
      }

      notifClear?.addEventListener('click', (event) => {
        event.preventDefault();
        clearNotifications();
      });

      async function refreshNotifications(){
        try {
          const response = await fetch('api/notifications.php');
          if (!response.ok) return;
          const data = await response.json();
          applyNotificationData(data);
        } catch (err) {
          console.error('Notifications unavailable', err);
        }
      }

      const requestNotificationRefresh = () => {
        refreshNotifications();
      };
      window.IBIN_refreshNotifications = requestNotificationRefresh;
      document.addEventListener('ibin:refresh-notifications', requestNotificationRefresh);

      // Themed confirmation modal for any element with data-confirm
      (function setupConfirmModal(){
        const modalEl = document.getElementById('ibinConfirmModal');
        const titleEl = document.getElementById('ibinConfirmTitle');
        const msgEl = document.getElementById('ibinConfirmMessage');
        const okBtn = document.getElementById('ibinConfirmOk');
        if (!modalEl || !okBtn || !titleEl || !msgEl) return;
        const modal = new bootstrap.Modal(modalEl, { backdrop: 'static' });
        let pendingEl = null;

        function resetModal(){
          pendingEl = null;
          titleEl.textContent = 'Are you sure?';
          msgEl.textContent = 'This action cannot be undone.';
        }

        okBtn.addEventListener('click', () => {
          if (!pendingEl) {
            modal.hide();
            return;
          }
          const el = pendingEl;
          pendingEl = null;
          modal.hide();
          const form = el.closest('form');
          const isLink = el.tagName === 'A' && el.getAttribute('href');

          // Temporarily disable confirm to avoid recursion
          const confirmValue = el.getAttribute('data-confirm');
          el.removeAttribute('data-confirm');

          if (form && (el.type === 'submit' || el.getAttribute('type') === 'submit')) {
            form.submit();
          } else if (isLink) {
            window.location.href = el.getAttribute('href');
          } else {
            el.click();
          }

          // Restore attribute for future clicks
          if (confirmValue) {
            el.setAttribute('data-confirm', confirmValue);
          }
        });

        modalEl.addEventListener('hidden.bs.modal', resetModal);

        document.addEventListener('click', (event) => {
          const trigger = event.target.closest('[data-confirm]');
          if (!trigger || modalEl.contains(trigger)) return;
          event.preventDefault();
          pendingEl = trigger;
          const title = trigger.getAttribute('data-confirm-title') || 'Are you sure?';
          const message = trigger.getAttribute('data-confirm') || 'This action cannot be undone.';
          titleEl.textContent = title;
          msgEl.textContent = message;
          modal.show();
        });
      })();

      function showToast(message, variant){
        if (!toastEl) return;
        toastEl.textContent = message;
        toastEl.className = `global-toast ${variant || 'info'} show`;
        setTimeout(() => toastEl.classList.remove('show'), 3200);
      }

      // Delay initial fetch slightly to improve first paint, then poll less frequently.
      setTimeout(refreshNotifications, 1500);
      notificationTimer = setInterval(refreshNotifications, 30000);
      document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
          if (notificationTimer) {
            clearInterval(notificationTimer);
            notificationTimer = null;
          }
        } else {
          refreshNotifications();
          if (!notificationTimer) {
            notificationTimer = setInterval(refreshNotifications, 30000);
          }
        }
      });
    })();
  </script>
</body>
</html>
