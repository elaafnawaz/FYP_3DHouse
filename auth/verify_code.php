<?php
// auth/verify_code.php
require_once __DIR__ . '/../includes/config.php';

// Check if email is in session
if (!isset($_SESSION['reset_email'])) {
    header('Location: forgot.php');
    exit;
}

$email = $_SESSION['reset_email'];
$errors = [];
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // Validate inputs
    if ($code === '' || strlen($code) !== 6 || !ctype_digit($code)) {
        $errors[] = 'Enter a valid 6-digit code.';
    }
    if ($password === '' || strlen($password) < 6) {
        $errors[] = 'Password required (min 6).';
    }
    if ($password !== $password2) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        if ($mysqli === null) {
            $errors[] = 'DB not available.';
        } else {
            // Verify code
            $sql = "SELECT email, expires_at FROM password_resets WHERE email = ? AND token = ? LIMIT 1";
            $stmt = $mysqli->prepare($sql);

            if ($stmt) {
                $stmt->bind_param('ss', $email, $code);
                $stmt->execute();
                $stmt->bind_result($db_email, $expires_at);

                if ($stmt->fetch()) {
                    $stmt->close();

                    // Check if expired
                    if (strtotime($expires_at) < time()) {
                        $errors[] = 'Code expired.';
                    } else {
                        // Update password
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $upd = $mysqli->prepare("UPDATE users SET password = ? WHERE email = ?");

                        if ($upd) {
                            $upd->bind_param('ss', $hash, $email);

                            if ($upd->execute()) {
                                // Delete all reset codes for this email
                                $del = $mysqli->prepare("DELETE FROM password_resets WHERE email = ?");
                                if ($del) {
                                    $del->bind_param('s', $email);
                                    $del->execute();
                                    $del->close();
                                }

                                // Clear session
                                unset($_SESSION['reset_email']);

                                // Success
                                $notice = 'success';
                            } else {
                                $errors[] = 'Failed to update password.';
                            }
                            $upd->close();
                        } else {
                            $errors[] = 'DB error.';
                        }
                    }
                } else {
                    $errors[] = 'Invalid code.';
                    $stmt->close();
                }
            } else {
                $errors[] = 'DB error.';
            }
        }
    }
}

$page_title = 'Verify Code - 3D VR House';
$extra_css = 'auth.css';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-page">
  <div class="auth-card">
    <h1>Enter verification code</h1>

    <?php if (!empty($errors)): ?>
      <div class="alert-danger">
        <ul style="margin:0;padding-left:1.1rem;">
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($notice === 'success'): ?>
      <div class="notice-box" style="background:#e9ffe9;border:1px solid #b6f0b6;padding:0.8rem;border-radius:8px;margin-bottom:0.8rem;text-align:center;">
        <p style="margin:0 0 0.5rem 0;font-size:1.1rem;font-weight:bold;color:#2d5016;">✓ Password Reset Successful!</p>
        <p style="margin:0 0 1rem 0;color:#555;">Redirecting to login in <span id="countdown">10</span> seconds...</p>
        <a href="<?= BASE_URL ?>auth/login.php" class="btn-primary" style="display:inline-block;text-decoration:none;padding:0.6rem 1.5rem;">Login Now</a>
      </div>
    <?php endif; ?>

    <?php if (empty($notice)): ?>
    <form method="post" novalidate>
      <div class="input-with-icon">
        <div class="left-icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
          </svg>
        </div>
        <input type="text" name="code" placeholder="Enter 6-digit code" maxlength="6" pattern="[0-9]{6}" required autofocus>
      </div>

      <div class="input-with-icon">
        <div class="left-icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17 8V7a5 5 0 0 0-10 0v1H5v12h14V8h-2ZM9 7a3 3 0 0 1 6 0v1H9Z"/>
          </svg>
        </div>
        <input type="password" name="password" placeholder="New password" required>
      </div>

      <div class="input-with-icon">
        <div class="left-icon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17 8V7a5 5 0 0 0-10 0v1H5v12h14V8h-2ZM9 7a3 3 0 0 1 6 0v1H9Z"/>
          </svg>
        </div>
        <input type="password" name="password2" placeholder="Confirm new password" required>
      </div>

      <div>
        <button type="submit" class="btn-primary">Reset password</button>
      </div>
    </form>
    <?php endif; ?>

  </div>

  <div class="side-image">
    <img src="<?= BASE_URL ?>assets/images/Housemodel.png" alt="Verify code side image" class="side-decor-image">
  </div>
</div>

<script>
<?php if ($notice === 'success'): ?>
// Auto-redirect countdown
let seconds = 10;
const countdownElement = document.getElementById('countdown');

const timer = setInterval(() => {
    seconds--;
    countdownElement.textContent = seconds;
    
    if (seconds <= 0) {
        clearInterval(timer);
        window.location.href = '<?= BASE_URL ?>auth/login.php';
    }
}, 1000);
<?php endif; ?>

// Notice fade out (for error messages)
setTimeout(() => {
    const notice = document.querySelector('.notice-box');
    if (notice && !document.getElementById('countdown')) { // Only fade if not success message
        notice.style.transition = "opacity 0.5s ease";
        notice.style.opacity = "0";

        setTimeout(() => {
            notice.remove();
        }, 500);
    }
}, 20000); // 20 seconds
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>