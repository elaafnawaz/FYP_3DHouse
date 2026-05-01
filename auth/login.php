<?php
// auth/login.php
require_once __DIR__ . '/../includes/config.php';

// Redirect if already logged in
if (!empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'dashboard/');
    exit;
}

$errors = [];
$email = '';

// If a remember cookie exists and session is empty, you may auto-login here (basic)
if (empty($_SESSION['user_id']) && !empty($_COOKIE['remember_user'])) {
    // Basic auto-login by id (dev convenience). For production use a secure token system.
    $remember_id = intval($_COOKIE['remember_user']);
    if ($remember_id > 0 && $mysqli !== null) {
        $sql = "SELECT id, first_name, last_name, role FROM users WHERE id = ? LIMIT 1";
        $s = $mysqli->prepare($sql);
        if ($s) {
            $s->bind_param('i', $remember_id);
            $s->execute();
            $s->bind_result($id, $first_name, $last_name, $role);
            if ($s->fetch()) {
                $_SESSION['user_id'] = $id;
                $_SESSION['user_name'] = $first_name . ' ' . $last_name;
                $_SESSION['role'] = $role ?? 'user';
                session_regenerate_id(true);
                $s->close();
                header('Location: ' . BASE_URL . 'dashboard/');
                exit;
            }
            $s->close();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $remember = !empty($_POST['remember']);

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email.';
    if ($password === '') $errors[] = 'Enter your password.';

    if (empty($errors)) {
        if ($mysqli === null) {
            $errors[] = 'Database connection not available.';
        } else {
            $sql = "SELECT id, first_name, last_name, password, role FROM users WHERE email = ? LIMIT 1";
            $stmt = $mysqli->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $stmt->bind_result($id, $first_name, $last_name, $hash, $role);
                if ($stmt->fetch()) {
                    if (password_verify($password, $hash)) {
                        $_SESSION['user_id'] = $id;
                        $_SESSION['user_name'] = $first_name . ' ' . $last_name;
                        $_SESSION['role'] = $role ?? 'user';
                        session_regenerate_id(true);
                        if ($remember) {
                            setcookie('remember_user', (string)$id, time() + 30*24*60*60, '/', '', false, true);
                        } else {
                            if (!empty($_COOKIE['remember_user'])) {
                                setcookie('remember_user', '', time() - 3600, '/', '', false, true);
                            }
                        }
                        $stmt->close();
                        header('Location: ' . BASE_URL . 'dashboard/');
                        exit;
                    } else {
                        $errors[] = 'Incorrect password. Please try again.';
                    }
                } else {
                    $errors[] = 'No account found with that email.';
                }
                $stmt->close();
            } else {
                $errors[] = 'Database error (prepare failed).';
            }
        }
    }
}

$page_title = 'Login - 3D VR House';
$extra_css = 'auth.css';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-page">
  <div class="side-image">
    <img src="<?= BASE_URL ?>assets/images/Housemodel.png" alt="Register side image" class="side-decor-image">
  </div>

  <div class="auth-card">

    <h1>Welcome Back</h1>

    <?php if (!empty($errors)): ?>
      <div class="alert-danger" id="errorBox">
        <?php foreach ($errors as $e): ?>
          <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" novalidate>
      <!-- Email with datalist for suggestions -->
      <div class="input-with-icon">
        <div class="left-icon" aria-hidden="true">
          <!-- Email Icon -->
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
            <path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 4-8 5L4 8V6l8 5 8-5Z"/>
          </svg>
        </div>
        <input list="email-suggestions" type="email" name="email" id="email" placeholder="Email" value="<?= htmlspecialchars($email) ?>" required>
        <datalist id="email-suggestions"></datalist>
      </div>

      <!-- Password -->
      <div class="input-with-icon">
        <div class="left-icon" aria-hidden="true">
          <!-- Lock Icon -->
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17 8V7a5 5 0 0 0-10 0v1H5v12h14V8h-2ZM9 7a3 3 0 0 1 6 0v1H9Z"/>
          </svg>
        </div>
        <input id="login_pwd" type="password" name="password" placeholder="Password" required>
        <div class="right-icon" onclick="togglePassword('login_pwd', this)">
          <!-- Eye Icon -->
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 5C7 5 3 9.5 3 12s4 7 9 7 9-4.5 9-7-4-7-9-7Zm0 10a3 3 0 1 1 0-6 3 3 0 0 1 0 6Z"/>
          </svg>
        </div>
      </div>

      <!-- remember + forgot row -->
      <div style="display:flex;align-items:center;justify-content:space-between;margin-top:6px;">
        <label style="display:flex;align-items:center;gap:8px;font-size:0.95rem;color:#22323f;">
          <input type="checkbox" name="remember" id="remember" value="1" style="width:16px;height:16px;">
          <span>Remember me</span>
        </label>

        <div>
          <a href="<?= BASE_URL ?>auth/forgot.php" style="color:#99c5ff;font-weight:600;text-decoration:none;">Forgot password?</a>
        </div>
      </div>

      <!-- Button -->
      <div style="margin-top:14px;">
        <button type="submit" class="btn-primary">Login</button>
      </div>

      <div class="bottom-link">
        Don't have an account? <a href="<?= BASE_URL ?>auth/register.php">Register</a>
      </div>
    </form>
  </div>
</div>

<!-- Password toggle and email suggestions autofill script -->
<script>
function togglePassword(id, btn) {
  const el = document.getElementById(id);
  if (!el) return;

  const isHidden = el.type === 'password';
  el.type = isHidden ? 'text' : 'password';

  // switch icon
  btn.innerHTML = isHidden
    ? `<svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" fill="currentColor" viewBox="0 0 24 24"><path d="M3.98 6.26 1.73 4 0 5.73l3.1 3.1C2.38 9.75 1.7 10.83 1.3 12c1.45 4.19 5.66 7 10.7 7 1.7 0 3.33-.35 4.8-.98l3.47 3.47L21.27 20 3.98 6.26zM12 17c-3.31 0-6.14-2.04-7.33-5 .32-.83.81-1.59 1.42-2.25l1.48 1.48A4.982 4.982 0 0 0 12 17zm0-10c3.31 0 6.14 2.04 7.33 5-.23.61-.54 1.19-.9 1.71l1.44 1.44A10.417 10.417 0 0 0 22.7 12c-1.45-4.19-5.66-7-10.7-7-.97 0-1.91.12-2.8.34l1.52 1.52c.41-.06.84-.1 1.28-.1z"/></svg>`
    : `<svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" fill="currentColor" viewBox="0 0 24 24"><path d="M12 5c-7 0-11 7-11 7s4 7 11 7 11-7 11-7-4-7-11-7zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10z"/></svg>`;
}

// Populate email suggestions
function populateEmailSuggestions() {
  const datalist = document.getElementById('email-suggestions');
  datalist.innerHTML = '';
  const savedCredentials = localStorage.getItem('savedCredentials');
  if (savedCredentials) {
    try {
      const credentials = JSON.parse(savedCredentials);
      Object.keys(credentials).forEach(email => {
        const option = document.createElement('option');
        option.value = email;
        datalist.appendChild(option);
      });
    } catch (e) {
      console.error('Error parsing saved credentials');
    }
  }
}

// Autofill password when email is selected
function autofillPassword() {
  const emailInput = document.getElementById('email');
  const passwordInput = document.getElementById('login_pwd');
  const email = emailInput?.value.trim().toLowerCase();
  if (email && passwordInput) {
    const savedCredentials = localStorage.getItem('savedCredentials');
    if (savedCredentials) {
      try {
        const credentials = JSON.parse(savedCredentials);
        if (credentials[email]) {
          passwordInput.value = credentials[email];
        }
      } catch (e) {
        console.error('Error parsing saved credentials');
      }
    }
  }
}

document.addEventListener('DOMContentLoaded', () => {
  // Populate email suggestions on page load
  populateEmailSuggestions();

  const emailInput = document.getElementById('email');
  const passwordInput = document.getElementById('login_pwd');
  const rememberCheckbox = document.getElementById('remember');

  // Autofill password when email changes or loses focus
  if (emailInput) {
    emailInput.addEventListener('change', autofillPassword);
    emailInput.addEventListener('blur', autofillPassword);
  }

  // Save credentials on form submit
  const form = document.querySelector('form');
  if (form) {
    form.addEventListener('submit', function() {
      const email = emailInput?.value.trim().toLowerCase() || '';
      const password = passwordInput?.value || '';
      const remember = rememberCheckbox?.checked;

      if (remember && email && password) {
        let credentials = {};
        const saved = localStorage.getItem('savedCredentials');
        if (saved) {
          try {
            credentials = JSON.parse(saved);
          } catch (e) {
            credentials = {};
          }
        }
        credentials[email] = password;
        localStorage.setItem('savedCredentials', JSON.stringify(credentials));
      } else if (!remember && email) {
        const saved = localStorage.getItem('savedCredentials');
        if (saved) {
          try {
            const credentials = JSON.parse(saved);
            delete credentials[email];
            localStorage.setItem('savedCredentials', JSON.stringify(credentials));
          } catch (e) {}
        }
      }
    });
  }
});
</script>
