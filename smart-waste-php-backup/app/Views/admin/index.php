<?php
// app/Views/admin/index.php
?>
<div class="row g-3">
  <div class="col-lg-5">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h5 class="card-title d-flex align-items-center gap-2">
          <i class="bi bi-person-fill-gear text-success"></i>
          Create Admin
        </h5>
        <p class="text-body-secondary small mb-3">Only the master admin can add additional admin accounts.</p>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
              <?php foreach ($errors as $err): ?>
                <li><?php echo htmlspecialchars($err); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php if ($success): ?>
          <div class="alert alert-success" role="alert">
            <?php echo htmlspecialchars($success); ?>
          </div>
        <?php endif; ?>

        <form method="post" class="needs-validation" novalidate>
          <div class="mb-3">
            <label for="name" class="form-label">Full name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            <div class="invalid-feedback">Enter the admin's name.</div>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            <div class="invalid-feedback">Enter a valid email.</div>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required minlength="8">
            <div class="invalid-feedback">Password must be at least 8 characters.</div>
          </div>
          <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
            <div class="invalid-feedback">Passwords must match.</div>
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-success">Create admin</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="card shadow-sm mb-3">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="card-title mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-person-plus-fill text-success"></i>
            Access Requests
          </h5>
          <span class="badge bg-success-subtle text-success-emphasis"><?php echo count($requests); ?> pending</span>
        </div>
        <p class="text-body-secondary small mb-3">Approve or reject new account requests submitted through the public form.</p>
        <?php if ($requestMessage): ?>
          <div class="alert alert-<?php echo htmlspecialchars($requestMessageType ?: 'info'); ?> py-2">
            <?php echo htmlspecialchars($requestMessage); ?>
          </div>
        <?php endif; ?>
        <?php if ($requestsError): ?>
          <div class="alert alert-warning mb-0"><?php echo htmlspecialchars($requestsError); ?></div>
        <?php elseif (empty($requests)): ?>
          <div class="text-secondary small">No pending requests.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr>
                  <th scope="col">Name</th>
                  <th scope="col">Email</th>
                  <th scope="col">Role</th>
                  <th scope="col">Submitted</th>
                  <th scope="col" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($requests as $request): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($request['name'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($request['email'] ?? ''); ?></td>
                    <td>
                      <span class="badge bg-secondary-subtle text-secondary-emphasis">
                        <?php echo htmlspecialchars($request['role'] ?? 'User'); ?>
                      </span>
                    </td>
                    <td class="small text-secondary">
                      <?php
                        $submitted = $request['submitted_at'] ?? '';
                        echo $submitted ? htmlspecialchars($submitted) : '—';
                      ?>
                    </td>
                    <td class="text-end">
                      <form method="post" class="d-flex flex-wrap gap-2 justify-content-end">
                        <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['id'] ?? ''); ?>">
                        <input type="text" class="form-control form-control-sm w-auto" name="reason" placeholder="Reason (optional)">
                        <button type="submit" name="action" value="approve_request" class="btn btn-success btn-sm">
                          Approve
                        </button>
                        <button type="submit" name="action" value="reject_request" class="btn btn-outline-danger btn-sm">
                          Reject
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <?php if ($accountMessage): ?>
          <div class="alert alert-<?php echo htmlspecialchars($accountMessageType ?: 'info'); ?> mb-3">
            <?php echo htmlspecialchars($accountMessage); ?>
          </div>
        <?php endif; ?>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="card-title mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-people-fill text-success"></i>
            Current Admins
          </h5>
          <small class="text-secondary">Master admin only</small>
        </div>
        <?php if ($listError): ?>
          <div class="alert alert-warning mb-0"><?php echo htmlspecialchars($listError); ?></div>
        <?php elseif (empty($admins)): ?>
          <div class="text-secondary small mb-4">No admin accounts found.</div>
        <?php else: ?>
          <div class="list-group account-list mb-4">
            <?php foreach ($admins as $admin): ?>
              <?php
                $email = $admin['email'] ?? '';
                $isMaster = $email !== '' && strcasecmp($email, auth_master_admin_email()) === 0;
                $adminId = $admin['id'] ?? '';
              ?>
              <div class="list-group-item account-entry d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2">
                <div class="flex-fill">
                  <div class="fw-semibold"><?php echo htmlspecialchars($admin['name'] ?? 'Admin'); ?></div>
                  <div class="text-secondary small"><?php echo htmlspecialchars($email); ?></div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <span class="badge role-badge <?php echo $isMaster ? 'role-master' : 'role-admin'; ?>">
                    <?php echo $isMaster ? 'Master Admin' : 'Admin'; ?>
                  </span>
                  <span class="badge status-badge <?php echo (strtolower($admin['status'] ?? '') === 'active') ? 'status-active' : 'status-inactive'; ?>">
                    <?php echo htmlspecialchars($admin['status'] ?? 'Unknown'); ?>
                  </span>
                  <span class="text-secondary small">
                    <?php
                      $lastLogin = $admin['last_login_at'] ?? '';
                      echo $lastLogin ? htmlspecialchars($lastLogin) : '—';
                    ?>
                  </span>
                </div>
                <div class="ms-md-auto">
                  <?php if (!$isMaster): ?>
                    <form method="post" class="d-inline">
                      <input type="hidden" name="action" value="delete_user">
                      <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($adminId); ?>">
                      <button type="submit"
                        class="btn btn-outline-danger btn-sm"
                        data-confirm="Delete this account? This action cannot be undone."
                        data-confirm-title="Delete account">
                        Delete
                      </button>
                    </form>
                  <?php else: ?>
                    <span class="text-secondary small">Protected</span>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="card-title mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-truck text-success"></i>
            Current Operators
          </h5>
        </div>
        <?php if (empty($operators)): ?>
          <div class="text-secondary small mb-4">No operator accounts found.</div>
        <?php else: ?>
          <div class="list-group account-list mb-4">
            <?php foreach ($operators as $operator): ?>
              <?php $operatorId = $operator['id'] ?? ''; ?>
              <div class="list-group-item account-entry d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2">
                <div class="flex-fill">
                  <div class="fw-semibold"><?php echo htmlspecialchars($operator['name'] ?? 'Operator'); ?></div>
                  <div class="text-secondary small"><?php echo htmlspecialchars($operator['email'] ?? ''); ?></div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <span class="badge role-badge role-operator">Operator</span>
                  <span class="badge status-badge <?php echo (strtolower($operator['status'] ?? '') === 'active') ? 'status-active' : 'status-inactive'; ?>">
                    <?php echo htmlspecialchars($operator['status'] ?? 'Unknown'); ?>
                  </span>
                  <span class="text-secondary small">
                    <?php
                      $lastLogin = $operator['last_login_at'] ?? '';
                      echo $lastLogin ? htmlspecialchars($lastLogin) : '—';
                    ?>
                  </span>
                </div>
                <div class="ms-md-auto">
                  <form method="post" class="d-inline">
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($operatorId); ?>">
                    <button type="submit"
                      class="btn btn-outline-danger btn-sm"
                      data-confirm="Delete this account? This action cannot be undone."
                      data-confirm-title="Delete account">
                      Delete
                    </button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="card-title mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-person text-success"></i>
            Current Users
          </h5>
        </div>
        <?php if (empty($users)): ?>
          <div class="text-secondary small">No user accounts found.</div>
        <?php else: ?>
          <div class="list-group account-list">
            <?php foreach ($users as $user): ?>
              <?php $userId = $user['id'] ?? ''; ?>
              <div class="list-group-item account-entry d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2">
                <div class="flex-fill">
                  <div class="fw-semibold"><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></div>
                  <div class="text-secondary small"><?php echo htmlspecialchars($user['email'] ?? ''); ?></div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <span class="badge role-badge role-user">User</span>
                  <span class="badge status-badge <?php echo (strtolower($user['status'] ?? '') === 'active') ? 'status-active' : 'status-inactive'; ?>">
                    <?php echo htmlspecialchars($user['status'] ?? 'Unknown'); ?>
                  </span>
                  <span class="text-secondary small">
                    <?php
                      $lastLogin = $user['last_login_at'] ?? '';
                      echo $lastLogin ? htmlspecialchars($lastLogin) : '—';
                    ?>
                  </span>
                </div>
                <div class="ms-md-auto">
                  <form method="post" class="d-inline">
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userId); ?>">
                    <button type="submit"
                      class="btn btn-outline-danger btn-sm"
                      data-confirm="Delete this account? This action cannot be undone."
                      data-confirm-title="Delete account">
                      Delete
                    </button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
  (() => {
    const form = document.querySelector('form.needs-validation');
    if (!form) return;
    form.addEventListener('submit', (event) => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    });
  })();
</script>
