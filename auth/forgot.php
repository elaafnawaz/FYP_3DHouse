<?php
// auth/forgot.php
require_once __DIR__ . '/../includes/config.php';

require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';
require __DIR__ . '/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$notice = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = strtolower(trim($_POST['email'] ?? ''));

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email.';
    } elseif ($mysqli === null) {
        $errors[] = 'Database connection not available.';
    } else {

        // check user exists
        $sql = "SELECT id FROM users WHERE email = ? LIMIT 1";
        $stmt = $mysqli->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();

            // always continue (security: don't reveal user existence)

            $stmt->close();

            // generate token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600);

            // save token
            $ins = $mysqli->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            if ($ins) {
                $ins->bind_param('sss', $email, $token, $expires);
                $ins->execute();
                $ins->close();

                // RESET LINK
                $resetUrl = rtrim(BASE_URL, '/') . "/auth/reset_password.php?token=" . urlencode($token);

                // SEND EMAIL USING GMAIL SMTP
                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;

                    // 🔴 CHANGE THESE
                    $mail->Username = 'nawazelaaf@gmail.com';
                    $mail->Password = 'wcex uuce zufu hyyn';

                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('YOUR_EMAIL@gmail.com', '3D VR House System');
                    $mail->addAddress($email);

                    $mail->isHTML(true);
                    $mail->Subject = 'Password Reset Request';

                    $mail->Body = "
                        <h3>Password Reset</h3>
                        <p>Click the link below to reset your password:</p>
                        <a href='$resetUrl'>Reset Password</a>
                        <p>This link will expire in 1 hour.</p>
                    ";

                    $mail->send();

                    $notice = "Reset link has been sent to your email.";

                } catch (Exception $e) {
                    $errors[] = "Email could not be sent. Error: " . $mail->ErrorInfo;
                }

            } else {
                $errors[] = 'Error creating reset token.';
            }
        } else {
            $errors[] = 'Database error.';
        }
    }
}

$page_title = 'Forgot password - 3D VR House';
$extra_css = 'auth.css';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-page">
  <div class="auth-card">
    <h1>Reset password</h1>

    <?php if (!empty($errors)): ?>
      <div class="alert-danger">
        <ul style="margin:0;padding-left:1.1rem;">
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($notice): ?>
      <div class="notice-box" style="background:#e9ffe9;border:1px solid #b6f0b6;padding:0.8rem;border-radius:8px;margin-bottom:0.8rem;">
        <?= htmlspecialchars($notice) ?>
      </div>
    <?php endif; ?>

    <form method="post">
      <div class="input-with-icon">
  <div class="left-icon" aria-hidden="true">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
      <path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 4-8 5L4 8V6l8 5 8-5Z"/>
    </svg>
  </div>
  <input type="email" name="email" placeholder="Enter your account email" required>
</div>
      <button type="submit" class="btn-primary">Send reset link</button>
    </form>

  </div>
</div>

<script>
setTimeout(() => {
    const notice = document.querySelector('.notice-box');
    if (notice) {
        notice.style.transition = "opacity 0.5s ease";
        notice.style.opacity = "0";

        setTimeout(() => {
            notice.remove();
        }, 500);
    }
}, 20000); // 20 seconds
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>