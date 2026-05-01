<?php
// auth/register.php
require_once __DIR__ . '/../includes/config.php';

// If already logged in, redirect to dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'dashboard/');
    exit;
}

$errors = [];
$success = '';
$old = ['first_name'=>'','last_name'=>'','email'=>'','phone_no'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic sanitization
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = strtolower(trim($_POST['email'] ?? ''));
    $phone_no   = trim($_POST['phone_no'] ?? '');
    $password   = $_POST['password'] ?? '';
    $password2  = $_POST['password2'] ?? '';

    // Keep old values for repopulation
    $old = ['first_name'=>$first_name,'last_name'=>$last_name,'email'=>$email,'phone_no'=>$phone_no];

    // Server-side validation

    // Names: allow Unicode letters, spaces, apostrophes and hyphens (1..60 chars)
    if ($first_name === '' || !preg_match('/^[\p{L} \'\-]{1,60}$/u', $first_name)) {
        $errors[] = 'First name must contain only letters, spaces, apostrophes or hyphens.';
    }
    if ($last_name === '' || !preg_match('/^[\p{L} \'\-]{1,60}$/u', $last_name)) {
        $errors[] = 'Last name must contain only letters, spaces, apostrophes or hyphens.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if ($password === '' || strlen($password) < 6) $errors[] = 'Password is required (min 6 characters).';
    if ($password !== $password2) $errors[] = 'Passwords do not match.';

    // Phone: allow empty OR digits only between 9 and 13 (adjust as needed)
    if ($phone_no === '' || !preg_match('/^[0-9]{9,13}$/', $phone_no)) {
        $errors[] = 'Enter a valid phone number (9–13 digits).';
    }

    // If no validation errors, proceed
    if (empty($errors)) {
        if ($mysqli === null) {
            $errors[] = 'Database connection not available.';
        } else {
            // Check existing email
            $sql = "SELECT id FROM users WHERE email = ? LIMIT 1";
            $stmt = $mysqli->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $errors[] = 'Email is already registered.';
                }
                $stmt->close();
            } else {
                $errors[] = 'Database error (prepare failed).';
            }
        }
    }

    if (empty($errors)) {
        // Insert user
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $insert = "INSERT INTO users (first_name, last_name, email, phone_no, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($insert);
        if ($stmt) {
            $stmt->bind_param('sssss', $first_name, $last_name, $email, $phone_no, $hash);
            if ($stmt->execute()) {
                // Registration successful - show success message and clear form
                $success = 'Registration successful! You can now login with your credentials.';
                // Clear old values
                $old = ['first_name'=>'','last_name'=>'','email'=>'','phone_no'=>''];
            } else {
                $errors[] = 'Registration failed. Please try again later.';
            }
            $stmt->close();
        } else {
            $errors[] = 'Database error (prepare failed).';
        }
    }
}

$page_title = 'Sign up - 3D VR House';
$extra_css = 'auth.css';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-page">
  <div class="side-image">
    <img src="<?= BASE_URL ?>assets/images/Housemodel.png" alt="Login side image" class="side-decor-image">
  </div>

  <div class="auth-card">

    <h1>Create Account</h1>

    <?php if (!empty($errors)): ?>
      <div class="alert-danger" id="errorBox">
        <?php foreach ($errors as $e): ?>
          <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert-success" id="successBox">
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <form method="post" novalidate id="registerForm">
      <div class="input-with-icon">
        <div class="left-icon">
          <!-- User icon -->
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-3.33 0-10 1.67-10 5v1h20v-1c0-3.33-6.67-5-10-5Z"/></svg>
        </div>

        <input id="first_name" type="text" name="first_name" placeholder="First Name"
               value="<?= htmlspecialchars($old['first_name']) ?>" required
               pattern="[\p{L} '\-]{1,60}" maxlength="60" inputmode="text"
               title="Use letters, spaces, apostrophes or hyphens only.">
      </div>

      <div class="input-with-icon">
        <div class="left-icon">
          <!-- User icon -->
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-3.33 0-10 1.67-10 5v1h20v-1c0-3.33-6.67-5-10-5Z"/></svg>
        </div>

        <input id="last_name" type="text" name="last_name" placeholder="Last Name"
               value="<?= htmlspecialchars($old['last_name']) ?>" required
               pattern="[\p{L} '\-]{1,60}" maxlength="60" inputmode="text"
               title="Use letters, spaces, apostrophes or hyphens only.">
      </div>

      <div class="input-with-icon">
        <div class="left-icon">
          <!-- Email icon -->
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
            <path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 4-8 5L4 8V6l8 5 8-5Z"/></svg>
        </div>
        <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($old['email']) ?>" required>
      </div>

      <div class="input-with-icon">
        <div class="left-icon">
          <!-- Phone icon -->
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17.707 12.293a1 1 0 0 0-1.414 0l-2.586 2.586a11.026 11.026 0 0 1-4.586-4.586l2.586-2.586a1 1 0 0 0 0-1.414L9.293 3.293a1 1 0 0 0-1.414 0l-2 2a2 2 0 0 0-.444 2.062 15.032 15.032 0 0 0 12.21 12.21 2 2 0 0 0 2.062-.444l2-2a1 1 0 0 0 0-1.414Z"/></svg>
        </div>

        <input
          id="phone_no"
          type="tel"
          name="phone_no"
          placeholder="Phone Number"
          value="<?= htmlspecialchars($old['phone_no']) ?>"
          required
          pattern="[0-9]{9,13}"
          maxlength="20"
          inputmode="numeric"
          autocomplete="tel"/>
      </div>

      <div class="input-with-icon">
        <div class="left-icon">
          <!-- Lock icon -->
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17 8V7a5 5 0 0 0-10 0v1H5v12h14V8ZM9 7a3 3 0 0 1 6 0v1H9Z"/></svg>
        </div>
        <input id="pwd" type="password" name="password" placeholder="Password" required>
        <div class="right-icon" onclick="togglePassword('pwd', this)"></div>
      </div>

      <div class="input-with-icon">
        <div class="left-icon">
          <!-- Lock icon -->
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17 8V7a5 5 0 0 0-10 0v1H5v12h14V8ZM9 7a3 3 0 0 1 6 0v1H9Z"/></svg>
        </div>
        <input id="pwd2" type="password" name="password2" placeholder="Confirm Password" required>
        <div class="right-icon" onclick="togglePassword('pwd2', this)"></div>
      </div>

      <button type="submit" class="btn-primary">Register</button>

      <div class="bottom-link">
        Already have an account? <a href="<?= BASE_URL ?>auth/login.php">Login</a>
      </div>
    </form>
  </div>
</div>

<!-- JS: eye toggle, names cleaning, phone cleaning, auto-hide error -->
<script>
function makeEyeOpenSVG() {
  return `<svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" fill="currentColor" viewBox="0 0 24 24">
    <path d="M12 5c-7 0-11 7-11 7s4 7 11 7 11-7 11-7-4-7-11-7zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10z"/>
  </svg>`;
}
function makeEyeSlashSVG() {
  return `<svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" fill="currentColor" viewBox="0 0 24 24">
    <path d="M3.98 6.26 1.73 4 0 5.73l3.1 3.1C2.38 9.75 1.7 10.83 1.3 12c1.45 4.19 5.66 7 10.7 7 1.7 0 3.33-.35 4.8-.98l3.47 3.47L21.27 20 3.98 6.26zM12 17c-3.31 0-6.14-2.04-7.33-5 .32-.83.81-1.59 1.42-2.25l1.48 1.48A4.982 4.982 0 0 0 12 17zm0-10c3.31 0 6.14 2.04 7.33 5-.23.61-.54 1.19-.9 1.71l1.44 1.44A10.417 10.417 0 0 0 22.7 12c-1.45-4.19-5.66-7-10.7-7-.97 0-1.91.12-2.8.34l1.52 1.52c.41-.06.84-.1 1.28-.1z"/>
  </svg>`;
}
function togglePassword(id, btn) {
  const el = document.getElementById(id);
  if (!el) return;
  const isHidden = el.type === 'password';
  el.type = isHidden ? 'text' : 'password';
  btn.innerHTML = isHidden ? makeEyeSlashSVG() : makeEyeOpenSVG();
}

document.addEventListener('DOMContentLoaded', () => {
  // init eye icons
  document.querySelectorAll('.right-icon').forEach(btn => {
    btn.innerHTML = makeEyeOpenSVG();
    btn.style.cursor = 'pointer';
  });

  // auto-hide error after 10s
  const errorBox = document.getElementById('errorBox');
  if (errorBox) {
    setTimeout(() => {
      errorBox.style.transition = 'opacity 0.8s ease';
      errorBox.style.opacity = '0';
      setTimeout(() => errorBox.remove(), 800);
    }, 10000);
  }

  // auto-hide success after 10s
  const successBox = document.getElementById('successBox');
  if (successBox) {
    setTimeout(() => {
      successBox.style.transition = 'opacity 0.8s ease';
      successBox.style.opacity = '0';
      setTimeout(() => successBox.remove(), 800);
    }, 10000);
  }

  // Name fields: remove anything that is not a Unicode letter, space, apostrophe or hyphen
  ['first_name', 'last_name'].forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    const cleanName = () => {
      const pos = el.selectionStart;
      const before = el.value;
      const cleaned = before.replace(/[^\p{L} '\-]+/gu, '');
      if (cleaned !== before) {
        el.value = cleaned;
        el.setSelectionRange(Math.max(0,pos-1), Math.max(0,pos-1));
      }
    };
    el.addEventListener('input', cleanName);
    el.addEventListener('paste', () => setTimeout(cleanName, 0));
  });

  // Phone: keep digits only while typing (if phone input exists)
  const phone = document.getElementById('phone_no');
  if (phone) {
    const cleanPhone = () => {
      const pos = phone.selectionStart;
      const before = phone.value;
      const cleaned = before.replace(/\D+/g, '');
      if (cleaned !== before) {
        phone.value = cleaned;
        phone.setSelectionRange(Math.max(0,pos-1), Math.max(0,pos-1));
      }
    };
    phone.addEventListener('input', cleanPhone);
    phone.addEventListener('paste', () => setTimeout(cleanPhone, 0));
  }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>