<?php
// dashboard/index.php
require_once __DIR__ . '/../includes/config.php';

// Auth check
if (empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

// safe helpers to query counts (works even if tables missing)
function safe_count($mysqli, $table) {
    if ($mysqli === null) return 0;
    // check table exists
    $res = $mysqli->query("SHOW TABLES LIKE '" . $mysqli->real_escape_string($table) . "'");
    if (!$res || $res->num_rows === 0) return 0;
    $r = $mysqli->query("SELECT COUNT(*) AS cnt FROM `" . $mysqli->real_escape_string($table) . "`");
    if (!$r) return 0;
    $row = $r->fetch_assoc();
    return intval($row['cnt'] ?? 0);
}

$userCount = safe_count($mysqli, 'users');
$sceneCount = safe_count($mysqli, 'scenes'); // your Unity scenes table name (if exists)
$resetCount = safe_count($mysqli, 'password_resets'); // example

// fetch last 5 users (if table exists)
$recentUsers = [];
if ($mysqli !== null) {
    $res = $mysqli->query("SHOW TABLES LIKE 'users'");
    if ($res && $res->num_rows > 0) {
        $q = $mysqli->query("SELECT id, first_name, last_name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5");
        if ($q) {
            while ($r = $q->fetch_assoc()) $recentUsers[] = $r;
        }
    }
}

// page variables for header
$page_title = 'Dashboard - 3D VR House';
$extra_css = 'dashboard.css';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrap container">
  <div class="dash-header">
    <div>
      <h1>Welcome back, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></h1>
      <p class="muted">Overview of your project & quick actions</p>
    </div>

    <div class="header-actions">
      <a class="btn-outline" href="<?= BASE_URL ?>">Home</a>
      <a class="btn-primary" href="<?= BASE_URL ?>unity/demo.php">Open demo</a>
    </div>
  </div>

  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-title">Users</div>
      <div class="stat-value"><?= $userCount ?></div>
      <div class="stat-note">Total registered users</div>
    </div>

    <div class="stat-card">
      <div class="stat-title">Scenes</div>
      <div class="stat-value"><?= $sceneCount ?></div>
      <div class="stat-note">Unity scenes (if any)</div>
    </div>

    <div class="stat-card">
      <div class="stat-title">Reset tokens</div>
      <div class="stat-value"><?= $resetCount ?></div>
      <div class="stat-note">Password reset requests</div>
    </div>

    <div class="stat-card">
      <div class="stat-title">Your role</div>
      <div class="stat-value"><?= htmlspecialchars($_SESSION['role'] ?? 'user') ?></div>
      <div class="stat-note">Access level</div>
    </div>
  </div>

  <div class="two-col">
    <div class="panel">
      <h3>Quick actions</h3>
      <div class="actions">
        <a class="action" href="<?= BASE_URL ?>unity/demo.php">
          <div class="action-icon">▶</div>
          <div class="action-label">Open Unity demo</div>
        </a>

        <a class="action" href="<?= BASE_URL ?>unity/upload.php">
          <div class="action-icon">⤴</div>
          <div class="action-label">Upload scene</div>
        </a>

        <a class="action" href="<?= BASE_URL ?>auth/register.php">
          <div class="action-icon">＋</div>
          <div class="action-label">Add user</div>
        </a>
      </div>
    </div>

    <div class="panel">
      <h3>Recent users</h3>

      <?php if (empty($recentUsers)): ?>
        <div class="empty">No users to show yet.</div>
      <?php else: ?>
        <table class="compact-table">
          <thead>
            <tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th></tr>
          </thead>
          <tbody>
            <?php foreach ($recentUsers as $u): ?>
              <tr>
                <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['role'] ?? 'user') ?></td>
                <td><?= htmlspecialchars($u['created_at']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

  <div class="panel footer-panel">
    <div class="muted">Tip: change a user's role to <code>admin</code> from phpMyAdmin to see admin-only UI sections.</div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
