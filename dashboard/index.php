<?php
// dashboard/index.php
require_once __DIR__ . '/../includes/config.php';

// Auth check
if (empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

// safe helpers to query counts
function safe_count($mysqli, $table) {
    if ($mysqli === null) return 0;
    $res = $mysqli->query("SHOW TABLES LIKE '" . $mysqli->real_escape_string($table) . "'");
    if (!$res || $res->num_rows === 0) return 0;

    $r = $mysqli->query("SELECT COUNT(*) AS cnt FROM `" . $mysqli->real_escape_string($table) . "`");
    if (!$r) return 0;

    $row = $r->fetch_assoc();
    return intval($row['cnt'] ?? 0);
}

$userCount  = safe_count($mysqli, 'users');
$sceneCount = safe_count($mysqli, 'scenes');
$resetCount = safe_count($mysqli, 'password_resets');

// fetch last 5 users
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

// page variables
$page_title = 'Dashboard - 3D VR House';
$extra_css = 'dashboard.css';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrap container">

  <!-- ✅ WELCOME HEADER -->
  <div class="dash-header">
    <div>
      <h1>
        Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?> 👋
      </h1>
      <p class="muted">
        Build, explore and manage your 3D house projects from here.
      </p>
    </div>

    <div class="header-actions">
      <a class="btn-outline" href="<?= BASE_URL ?>">Home</a>
      <a class="btn-primary" href="<?= BASE_URL ?>demo.php">View Demo</a>
    </div>
  </div>

  <!-- ✅ STATS -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-title">Users</div>
      <div class="stat-value"><?= $userCount ?></div>
      <div class="stat-note">Total registered users</div>
    </div>

    <div class="stat-card">
      <div class="stat-title">Scenes</div>
      <div class="stat-value"><?= $sceneCount ?></div>
      <div class="stat-note">Unity scenes</div>
    </div>

    <div class="stat-card">
      <div class="stat-title">Reset Tokens</div>
      <div class="stat-value"><?= $resetCount ?></div>
      <div class="stat-note">Password requests</div>
    </div>

    <div class="stat-card">
      <div class="stat-title">Your Role</div>
      <div class="stat-value"><?= htmlspecialchars($_SESSION['role'] ?? 'user') ?></div>
      <div class="stat-note">Access level</div>
    </div>
  </div>

  <!-- ✅ MAIN CONTENT -->
  <div class="two-col">

    <!-- QUICK ACTIONS -->
    <div class="panel">
      <h3>Quick actions</h3>

      <div class="actions">
        <a class="action" href="<?= BASE_URL ?>demo.php">
          <div class="action-icon">▶</div>
          <div class="action-label">View Demo</div>
        </a>

        <a class="action" href="#">
          <div class="action-icon">🏠</div>
          <div class="action-label">Create House</div>
        </a>

        <a class="action" href="<?= BASE_URL ?>auth/register.php">
          <div class="action-icon">＋</div>
          <div class="action-label">Add User</div>
        </a>
      </div>
    </div>

    <!-- RECENT USERS -->
    <div class="panel">
      <h3>Recent users</h3>

      <?php if (empty($recentUsers)): ?>
        <div class="empty">No users yet.</div>
      <?php else: ?>
        <table class="compact-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Joined</th>
            </tr>
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

  <!-- FOOTER TIP -->
  <div class="panel footer-panel">
    <div class="muted">
      Tip: Upgrade your dashboard by connecting user input to Unity scenes 🎯
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>