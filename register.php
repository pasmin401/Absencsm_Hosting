<?php
if (ob_get_level() === 0) ob_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

if (isLoggedIn()) {
    ob_end_clean();
    header('Location: dashboard.php');
    exit;
}

$error      = '';
$registered = false;
$fields     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'username'   => trim($_POST['username']   ?? ''),
        'email'      => trim($_POST['email']      ?? ''),
        'phone'      => trim($_POST['phone']      ?? ''),
        'department' => trim($_POST['department'] ?? ''),
        'password'   => $_POST['password']        ?? '',
        'confirm'    => $_POST['confirm']         ?? '',
    ];

    if (empty($fields['username']) || empty($fields['email']) || empty($fields['password'])) {
        $error = 'Username, email, and password are required.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $fields['username'])) {
        $error = 'Username must be 3–30 characters (letters, numbers, underscore only).';
    } elseif (!filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($fields['password']) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($fields['password'] !== $fields['confirm']) {
        $error = 'Passwords do not match.';
    } else {
        try {
            if (getUserByUsername($fields['username'])) {
                $error = 'That username is already taken. Please choose another.';
            } elseif (getUserByEmail($fields['email'])) {
                $error = 'This email is already registered. <a href="index.php" style="font-weight:700;color:var(--primary)">Try logging in →</a>';
            } else {
                $uid = createUser($fields['username'], $fields['email'], $fields['password'], 'user');
                $extra = [];
                if (!empty($fields['phone']))      $extra['phone']      = $fields['phone'];
                if (!empty($fields['department'])) $extra['department'] = $fields['department'];
                if ($extra) updateUser($uid, $extra);
                $registered = true;
                ob_end_clean();
                ob_start();
                header('Location: index.php?msg=registered');
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'Username or email already exists. <a href="index.php">Try logging in →</a>';
            } else {
                $error = 'Database error: ' . htmlspecialchars($e->getMessage());
            }
        } catch (Exception $e) {
            $error = 'Unexpected error: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register – <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<?php if ($registered): ?>
<meta http-equiv="refresh" content="2;url=index.php?msg=registered">
<?php endif; ?>
<style>
.auth-form-container { max-width: 480px; }
.form-row  { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
@media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }
.pwd-strength     { height: 4px; border-radius: 2px; margin-top: 6px; background: var(--border); overflow: hidden; }
.pwd-strength-bar { height: 100%; border-radius: 2px; transition: width .3s, background .3s; width: 0; }
.pwd-hint         { font-size: .75rem; margin-top: 4px; min-height: 16px; }
.success-box { background: var(--success-lt); border: 1.5px solid #6ee7b7; border-radius: var(--radius); padding: 20px; text-align: center; margin-bottom: 20px; }
.success-box .big-icon { font-size: 2.5rem; margin-bottom: 8px; }
.success-box h3 { color: #065f46; margin-bottom: 6px; font-size: 1.1rem; }
.success-box p  { color: #047857; font-size: .9rem; }
</style>
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-brand">
    <div class="logo-mark"><img src="assets/logo.svg" alt="Logo" style="width:48px;height:48px;object-fit:contain;border-radius:8px;"></div>
    <h1>Join<br><?= e(APP_NAME) ?><br>Today</h1>
    <p>Create your account and start tracking attendance with geo-verification and photo proof.</p>
    <div class="auth-features">
      <div class="auth-feature-item"><span>✅</span><span>Free to register</span></div>
      <div class="auth-feature-item"><span>🔒</span><span>Encrypted &amp; secure</span></div>
      <div class="auth-feature-item"><span>⚡</span><span>Ready in seconds</span></div>
    </div>
  </div>

  <div class="auth-form-side">
    <div class="auth-form-container">

      <?php if ($registered): ?>
        <div class="success-box">
          <div class="big-icon">🎉</div>
          <h3>Account created successfully!</h3>
          <p>Redirecting you to the login page…</p>
        </div>
        <p style="text-align:center;font-size:.875rem;color:var(--txt2)">
          Not redirected? <a href="index.php?msg=registered" style="font-weight:700">Click here →</a>
        </p>
      <?php else: ?>
        <h2>Create account</h2>
        <p class="subtitle">Fill in the details below to get started</p>

        <?php if ($error): ?>
          <div class="alert alert-danger">⚠️ <?= $error ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
          <div class="form-row">
            <div class="form-group">
              <label for="username">Username <span style="color:var(--danger)">*</span></label>
              <input type="text" id="username" name="username" value="<?= e($fields['username'] ?? '') ?>" placeholder="e.g. john_doe" required maxlength="30" autocomplete="username">
            </div>
            <div class="form-group">
              <label for="email">Email <span style="color:var(--danger)">*</span></label>
              <input type="email" id="email" name="email" value="<?= e($fields['email'] ?? '') ?>" placeholder="you@email.com" required autocomplete="email">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="phone">Phone Number</label>
              <input type="tel" id="phone" name="phone" value="<?= e($fields['phone'] ?? '') ?>" placeholder="+62 xxx-xxxx-xxxx">
            </div>
            <div class="form-group">
              <label for="department">Department</label>
              <input type="text" id="department" name="department" value="<?= e($fields['department'] ?? '') ?>" placeholder="e.g. Engineering">
            </div>
          </div>

          <div class="form-group">
            <label for="password">Password <span style="color:var(--danger)">*</span></label>
            <div class="input-wrap">
              <input type="password" id="password" name="password" class="has-icon" placeholder="Min. 6 characters" required autocomplete="new-password" oninput="checkStrength(this.value)">
              <span class="input-icon" onclick="togglePassword('password', this)" title="Show / hide">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </span>
            </div>
            <div class="pwd-strength"><div class="pwd-strength-bar" id="pwd-bar"></div></div>
            <div class="pwd-hint text-muted" id="pwd-hint">Use at least 6 characters</div>
          </div>

          <div class="form-group">
            <label for="confirm">Confirm Password <span style="color:var(--danger)">*</span></label>
            <div class="input-wrap">
              <input type="password" id="confirm" name="confirm" class="has-icon" placeholder="Repeat password" required autocomplete="new-password">
              <span class="input-icon" onclick="togglePassword('confirm', this)" title="Show / hide">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </span>
            </div>
            <div class="pwd-hint" id="match-hint"></div>
          </div>

          <button type="submit" class="btn btn-primary btn-full btn-lg">Create Account</button>
        </form>

        <div style="text-align:center;margin-top:20px;font-size:.875rem;color:var(--txt2)">
          Already have an account? <a href="index.php" style="font-weight:600">Sign in</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if ($registered): ?>
<script>window.location.replace('index.php?msg=registered');</script>
<?php endif; ?>

<script>
function togglePassword(inputId, iconEl) {
  const input = document.getElementById(inputId);
  const show  = (input.type === 'password');
  input.type  = show ? 'text' : 'password';
  iconEl.innerHTML = show
    ? `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`
    : `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
}
function checkStrength(val) {
  const bar  = document.getElementById('pwd-bar');
  const hint = document.getElementById('pwd-hint');
  if (!bar) return;
  let score = 0;
  if (val.length >= 6)           score++;
  if (val.length >= 10)          score++;
  if (/[A-Z]/.test(val))         score++;
  if (/[0-9]/.test(val))         score++;
  if (/[^A-Za-z0-9]/.test(val))  score++;
  const levels = [
    { w:  0, c: '#ef4444', t: '' },
    { w: 20, c: '#ef4444', t: 'Weak' },
    { w: 40, c: '#f59e0b', t: 'Fair' },
    { w: 60, c: '#f59e0b', t: 'Good' },
    { w: 80, c: '#10b981', t: 'Strong' },
    { w:100, c: '#10b981', t: 'Very strong ✓' },
  ];
  const lv = levels[score];
  bar.style.width      = lv.w + '%';
  bar.style.background = lv.c;
  hint.textContent     = lv.t;
  hint.style.color     = lv.c;
  checkMatch();
}
function checkMatch() {
  const pwd  = document.getElementById('password');
  const conf = document.getElementById('confirm');
  const hint = document.getElementById('match-hint');
  if (!pwd || !conf || !hint) return;
  if (!conf.value) { hint.textContent = ''; return; }
  if (pwd.value === conf.value) {
    hint.textContent = '✓ Passwords match'; hint.style.color = '#10b981';
  } else {
    hint.textContent = '✗ Passwords do not match'; hint.style.color = '#ef4444';
  }
}
const confirmEl = document.getElementById('confirm');
if (confirmEl) confirmEl.addEventListener('input', checkMatch);
</script>
</body>
</html>
