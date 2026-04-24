<?php
// auth/reset_password.php
require_once __DIR__ . '/../includes/config.php';

$token = isset($_GET['token']) ? trim($_GET['token']) : (isset($_POST['token']) ? trim($_POST['token']) : '');
$errors = [];
$notice = '';

if ($token === '' || strlen($token) < 20) {
    $errors[] = 'Invalid reset link. Please request a new one.';
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';
        if ($password === '' || strlen($password) < 6) $errors[] = 'Password required (min 6).';
        if ($password !== $password2) $errors[] = 'Passwords do not match.';

        if (empty($errors)) {
            if ($mysqli === null) {
                $errors[] = 'DB not available.';
            } else {
                // find token
                $sql = "SELECT email, expires_at FROM password_resets WHERE token = ? LIMIT 1";
                $stmt = $mysqli->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param('s', $token);
                    $stmt->execute();
                    $stmt->bind_result($email, $expires_at);
                    if ($stmt->fetch()) {
                        $stmt->close();
                        if (strtotime($expires_at) < time()) {
                            $errors[] = 'Token expired.';
                        } else {
                            // update user password
                            $hash = password_hash($password, PASSWORD_DEFAULT);
                            $upd = $mysqli->prepare("UPDATE users SET password = ? WHERE email = ?");
                            if ($upd) {
                                $upd->bind_param('ss', $hash, $email);
                                if ($upd->execute()) {
                                    // delete all tokens for this email
                                    $del = $mysqli->prepare("DELETE FROM password_resets WHERE email = ?");
                                    if ($del) {
                                        $del->bind_param('s', $email);
                                        $del->execute();
                                        $del->close();
                                    }
                                    $notice = 'Password reset successful. You can now <a href="' . BASE_URL . 'auth/login.php">login</a>.';
                                } else {
                                    $errors[] = 'Failed to update password.';
                                }
                                $upd->close();
                            } else {
                                $errors[] = 'DB error.';
                            }
                        }
                    } else {
                        $errors[] = 'Invalid token.';
                        $stmt->close();
                    }
                } else {
                    $errors[] = 'DB error.';
                }
            }
        }
    }
}

$page_title = 'Reset password - 3D VR House';
$extra_css = 'auth.css';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-page">
  <div class="auth-card">
    <h1>Set a new password</h1>

    <?php if (!empty($errors)): ?>
      <div class="alert-danger"><ul style="margin:0;padding-left:1.1rem;"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <?php if ($notice): ?>
      <div style="background:#eef9ff;border:1px solid #cfe9ff;padding:0.8rem;border-radius:8px;margin-bottom:0.8rem;color:#10304b">
        <?= $notice ?>
      </div>
    <?php endif; ?>

    <?php if (empty($notice)): ?>
    <form method="post" novalidate>
      <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
      <div class="input-with-icon">
        <div class="left-icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17 8V7a5 5 0 0 0-10 0v1H5v12h14V8h-2ZM9 7a3 3 0 0 1 6 0v1H9Z"/>
          </svg>
        </div>
        <input id="new_pwd" type="password" name="password" placeholder="New password" required>
      </div>

      <div class="input-with-icon">
        <div class="left-icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17 8V7a5 5 0 0 0-10 0v1H5v12h14V8h-2ZM9 7a3 3 0 0 1 6 0v1H9Z"/>
          </svg>
        </div>
        <input id="new_pwd2" type="password" name="password2" placeholder="Confirm new password" required>
      </div>

      <div>
        <button type="submit" class="btn-primary">Set new password</button>
      </div>
    </form>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

