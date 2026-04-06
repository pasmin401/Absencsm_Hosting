<?php
/**
 * AttendTrack – One-Click MySQL Installer
 * Visit this page once to create the database tables and seed data.
 * DELETE or RENAME this file after installation for security.
 */

$step    = $_GET['step'] ?? 'check';
$results = [];
$error   = '';

// ── Step 2: Run installation ──────────────────────────────────
if ($step === 'install') {
    $host    = trim($_POST['db_host']    ?? 'localhost');
    $dbname  = trim($_POST['db_name']    ?? 'attendtrack');
    $user    = trim($_POST['db_user']    ?? 'root');
    $pass    = $_POST['db_pass']         ?? '';
    $create  = isset($_POST['create_db']);

    try {
        // Connect without selecting a DB first if creating
        $dsn = "mysql:host={$host};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $results[] = ['✅', 'Connected to MySQL server'];

        if ($create) {
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $results[] = ['✅', "Database <strong>{$dbname}</strong> created (or already exists)"];
        }

        $pdo->exec("USE `{$dbname}`");
        $results[] = ['✅', "Using database <strong>{$dbname}</strong>"];

        // ── Create tables ─────────────────────────────────────
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `users` (
              `id`          INT           NOT NULL AUTO_INCREMENT,
              `username`    VARCHAR(30)   NOT NULL,
              `email`       VARCHAR(100)  NOT NULL,
              `password`    VARCHAR(255)  NOT NULL,
              `role`        ENUM('user','admin') NOT NULL DEFAULT 'user',
              `phone`       VARCHAR(20)   DEFAULT NULL,
              `department`  VARCHAR(100)  DEFAULT NULL,
              `work_start`  TIME          DEFAULT NULL,
              `work_end`    TIME          DEFAULT NULL,
              `profile_pic` MEDIUMTEXT    DEFAULT NULL,
              `is_active`   TINYINT(1)    NOT NULL DEFAULT 1,
              `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at`  DATETIME      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uq_username` (`username`),
              UNIQUE KEY `uq_email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results[] = ['✅', 'Table <strong>users</strong> ready'];

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `attendance` (
              `id`                INT           NOT NULL AUTO_INCREMENT,
              `user_id`           INT           NOT NULL,
              `work_date`         DATE          NOT NULL,
              `checkin_time`      TIME          DEFAULT NULL,
              `checkin_lat`       DECIMAL(10,6) DEFAULT NULL,
              `checkin_lng`       DECIMAL(10,6) DEFAULT NULL,
              `checkin_photo`     MEDIUMTEXT    DEFAULT NULL,
              `checkout_time`     TIME          DEFAULT NULL,
              `checkout_lat`      DECIMAL(10,6) DEFAULT NULL,
              `checkout_lng`      DECIMAL(10,6) DEFAULT NULL,
              `checkout_photo`    MEDIUMTEXT    DEFAULT NULL,
              `ot_checkin_time`   TIME          DEFAULT NULL,
              `ot_checkin_lat`    DECIMAL(10,6) DEFAULT NULL,
              `ot_checkin_lng`    DECIMAL(10,6) DEFAULT NULL,
              `ot_checkin_photo`  MEDIUMTEXT    DEFAULT NULL,
              `ot_checkout_time`  TIME          DEFAULT NULL,
              `ot_checkout_lat`   DECIMAL(10,6) DEFAULT NULL,
              `ot_checkout_lng`   DECIMAL(10,6) DEFAULT NULL,
              `ot_checkout_photo` MEDIUMTEXT    DEFAULT NULL,
              `status`            ENUM('present','absent','leave','holiday') NOT NULL DEFAULT 'present',
              `notes`             TEXT          DEFAULT NULL,
              `created_at`        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uq_user_date` (`user_id`, `work_date`),
              KEY `idx_work_date` (`work_date`),
              CONSTRAINT `fk_att_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results[] = ['✅', 'Table <strong>attendance</strong> ready'];

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `password_resets` (
              `id`         INT          NOT NULL AUTO_INCREMENT,
              `email`      VARCHAR(100) NOT NULL,
              `token`      VARCHAR(64)  NOT NULL,
              `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uq_token` (`token`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $results[] = ['✅', 'Table <strong>password_resets</strong> ready'];

        // ── Seed data ─────────────────────────────────────────
        $adminPwd = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // Admin@123
        $userPwd  = '$2y$10$TKh8H1.PfuA38Xe.aMtGtOQl8g/l4RVNRSO1Z3DBNZ9MUu4GnuUa'; // User@123

        $pdo->exec("INSERT IGNORE INTO `users` (username,email,password,role,department,is_active) VALUES
            ('admin','admin@attendtrack.com','{$adminPwd}','admin','Management',1)");

        $pdo->exec("INSERT IGNORE INTO `users` (username,email,password,role,phone,department,work_start,work_end,is_active) VALUES
            ('john_doe','john.doe@company.com','{$userPwd}','user','+62 812-0001-0001','Engineering','08:00:00','17:00:00',1),
            ('jane_smith','jane.smith@company.com','{$userPwd}','user','+62 812-0001-0002','Marketing','08:00:00','17:00:00',1),
            ('ali_rahman','ali.rahman@company.com','{$userPwd}','user','+62 812-0001-0003','Finance','09:00:00','18:00:00',1),
            ('siti_nurhaliza','siti.nurhaliza@company.com','{$userPwd}','user','+62 812-0001-0004','HR','08:00:00','17:00:00',1),
            ('budi_santoso','budi.santoso@company.com','{$userPwd}','user','+62 812-0001-0005','Operations','07:00:00','16:00:00',1)");

        $results[] = ['✅', 'Seed data inserted (admin + 5 sample users)'];

        // ── Verify config.php values ──────────────────────────
        $configPath = __DIR__ . '/config.php';
        $configOk = false;
        if (file_exists($configPath)) {
            $content = file_get_contents($configPath);
            if (strpos($content, "define('DB_NAME'") !== false) {
                // Auto-patch config.php with entered credentials
                $content = preg_replace("/define\('DB_HOST',\s*'[^']*'\)/",  "define('DB_HOST',  '{$host}')",   $content);
                $content = preg_replace("/define\('DB_NAME',\s*'[^']*'\)/",  "define('DB_NAME',  '{$dbname}')", $content);
                $content = preg_replace("/define\('DB_USER',\s*'[^']*'\)/",  "define('DB_USER',  '{$user}')",   $content);
                $content = preg_replace("/define\('DB_PASS',\s*'[^']*'\)/",  "define('DB_PASS',  '{$pass}')",   $content);
                file_put_contents($configPath, $content);
                $results[] = ['✅', '<strong>config.php</strong> updated with your database credentials'];
                $configOk = true;
            }
        }
        if (!$configOk) {
            $results[] = ['⚠️', 'Could not auto-update config.php — please edit it manually'];
        }

        $step = 'done';

    } catch (PDOException $e) {
        $error = $e->getMessage();
        $step  = 'error';
    }
}

// ── Step 1: Check environment ─────────────────────────────────
$phpOk    = version_compare(PHP_VERSION, '8.0', '>=');
$pdoOk    = extension_loaded('pdo_mysql');
$configExists = file_exists(__DIR__ . '/config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Install – AttendTrack</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: system-ui, sans-serif; background: #f0f4f8; color: #0f172a; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
.card { background: #fff; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,.08); padding: 40px; max-width: 560px; width: 100%; }
h1 { font-size: 1.6rem; font-weight: 700; margin-bottom: 8px; }
p.sub { color: #64748b; font-size: .95rem; margin-bottom: 28px; }
.check { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 8px; margin-bottom: 10px; font-size: .9rem; }
.check.ok  { background: #d1fae5; color: #065f46; }
.check.bad { background: #fee2e2; color: #991b1b; }
.form-group { margin-bottom: 16px; }
label { display: block; font-size: .85rem; font-weight: 600; color: #374151; margin-bottom: 6px; }
input[type=text], input[type=password] { width: 100%; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: .9rem; transition: border-color .2s; outline: none; }
input[type=text]:focus, input[type=password]:focus { border-color: #0891b2; }
.checkbox-row { display: flex; align-items: center; gap: 8px; font-size: .9rem; cursor: pointer; }
.checkbox-row input { width: 16px; height: 16px; accent-color: #0891b2; }
.btn { display: inline-block; padding: 12px 24px; border-radius: 10px; font-size: .95rem; font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: background .2s; }
.btn-primary { background: #0891b2; color: #fff; width: 100%; text-align: center; margin-top: 8px; }
.btn-primary:hover { background: #0e7490; }
.result-item { display: flex; align-items: flex-start; gap: 10px; padding: 8px 12px; border-radius: 8px; margin-bottom: 8px; background: #f8fafc; font-size: .9rem; }
.result-item .icon { flex-shrink: 0; font-size: 1.1rem; }
.alert-err { background: #fee2e2; border: 1.5px solid #fca5a5; border-radius: 10px; padding: 16px 20px; color: #991b1b; font-size: .9rem; margin-bottom: 20px; }
.success-box { background: #d1fae5; border: 1.5px solid #6ee7b7; border-radius: 10px; padding: 24px; text-align: center; margin-bottom: 24px; }
.success-box h2 { color: #065f46; margin-bottom: 8px; }
.success-box p { color: #047857; font-size: .9rem; }
.creds-table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: .88rem; }
.creds-table th { background: #f0f9ff; color: #0e7490; text-align: left; padding: 8px 12px; }
.creds-table td { padding: 8px 12px; border-bottom: 1px solid #e2e8f0; }
.creds-table code { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-family: monospace; }
.warning { background: #fef3c7; border: 1.5px solid #fcd34d; border-radius: 8px; padding: 12px 16px; font-size: .85rem; color: #92400e; margin-top: 16px; }
</style>
</head>
<body>
<div class="card">

<?php if ($step === 'done'): ?>
  <h1>🎉 Installation Complete!</h1>
  <p class="sub">AttendTrack has been installed successfully.</p>

  <div class="success-box">
    <h2>✅ All done!</h2>
    <p>Your database is set up and the app is ready to use.</p>
  </div>

  <div style="margin-bottom:20px">
    <?php foreach ($results as [$icon, $msg]): ?>
      <div class="result-item"><span class="icon"><?= $icon ?></span><span><?= $msg ?></span></div>
    <?php endforeach; ?>
  </div>

  <table class="creds-table">
    <tr><th colspan="2">🔑 Default Login Accounts</th></tr>
    <tr><td><strong>Admin</strong></td><td>Username: <code>admin</code> · Password: <code>Admin@123</code></td></tr>
    <tr><td><strong>User</strong></td><td>Username: <code>john_doe</code> · Password: <code>User@123</code></td></tr>
  </table>

  <div class="warning">
    ⚠️ <strong>Security:</strong> Delete or rename <code>install.php</code> after installation, and change all default passwords immediately.
  </div>

  <a href="index.php" class="btn btn-primary" style="margin-top:20px;display:block">→ Go to Login Page</a>

<?php elseif ($step === 'error'): ?>
  <h1>⚠️ Installation Failed</h1>
  <p class="sub">Something went wrong. Please check your credentials and try again.</p>
  <div class="alert-err">❌ <?= htmlspecialchars($error) ?></div>
  <a href="install.php" class="btn btn-primary">← Try Again</a>

<?php else: ?>
  <h1>⚙️ AttendTrack Installer</h1>
  <p class="sub">This will create the MySQL database tables and seed the initial data.</p>

  <!-- Requirements check -->
  <div class="check <?= $phpOk ? 'ok' : 'bad' ?>">
    <?= $phpOk ? '✅' : '❌' ?> PHP <?= PHP_VERSION ?> <?= $phpOk ? '(required: 8.0+)' : '— PHP 8.0 or higher required!' ?>
  </div>
  <div class="check <?= $pdoOk ? 'ok' : 'bad' ?>">
    <?= $pdoOk ? '✅' : '❌' ?> PDO MySQL extension <?= $pdoOk ? 'loaded' : '— pdo_mysql extension required!' ?>
  </div>
  <div class="check ok">
    ✅ config.php <?= $configExists ? 'found' : 'not found (will be created)' ?>
  </div>

  <hr style="border:none;border-top:1px solid #e2e8f0;margin:24px 0">

  <form method="POST" action="install.php?step=install">
    <div class="form-group">
      <label for="db_host">MySQL Host</label>
      <input type="text" id="db_host" name="db_host" value="localhost" placeholder="localhost">
    </div>
    <div class="form-group">
      <label for="db_name">Database Name</label>
      <input type="text" id="db_name" name="db_name" value="attendtrack" placeholder="attendtrack">
    </div>
    <div class="form-group">
      <label for="db_user">MySQL Username</label>
      <input type="text" id="db_user" name="db_user" value="root" placeholder="root">
    </div>
    <div class="form-group">
      <label for="db_pass">MySQL Password</label>
      <input type="password" id="db_pass" name="db_pass" placeholder="Leave blank if none">
    </div>
    <div class="form-group">
      <label class="checkbox-row">
        <input type="checkbox" name="create_db" value="1" checked>
        Create database if it doesn't exist
      </label>
    </div>
    <button type="submit" class="btn btn-primary">🚀 Run Installation</button>
  </form>
<?php endif; ?>

</div>
</body>
</html>
