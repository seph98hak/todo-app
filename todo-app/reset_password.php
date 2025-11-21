<?php
// reset_password.php
session_start();
require_once 'db.php';
require_once 'utils.php';

$message = '';
$token = $_GET['token'] ?? '';
if (empty($token)) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT id, reset_expiry FROM users WHERE reset_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user || strtotime($user['reset_expiry']) < time()) {
    $message = '<div class="alert alert-danger">Invalid or expired token.</div>';
    $user = null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if ($password !== $confirm || strlen($password) < 8) {
        $message = '<div class="alert alert-danger">Passwords must match and be at least 8 chars.</div>';
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
        $update->execute([$hashed, $user['id']]);
        $message = '<div class="alert alert-success">Password reset! <a href="index.php">Login</a></div>';
        $user = null;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Reset Password</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="card shadow mx-auto" style="max-width:520px;">
    <div class="card-body">
      <h4>Reset Password</h4>
      <?php echo $message; ?>
      <?php if ($user): ?>
      <form method="POST" data-validate>
        <div class="mb-3">
          <label class="form-label">New Password</label>
          <input type="password" name="password" class="form-control" minlength="8" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Confirm</label>
          <input type="password" name="confirm_password" class="form-control" minlength="8" required>
        </div>
        <button class="btn btn-success w-100">Reset Password</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<script src="assets/validation.js"></script>
</body>
</html>
