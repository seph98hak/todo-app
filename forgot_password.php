<?php
// forgot_password.php
session_start();
require_once 'db.php';
require_once 'utils.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="alert alert-danger">Enter a valid email.</div>';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $update = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE id = ?");
            $update->execute([$token, $expiry, $user['id']]);

            $link = "http://localhost/todo-app/reset_password.php?token=$token";
            simulate_email($email, "Password Reset", "Click this link to reset your password: $link");
            $message = '<div class="alert alert-success">Check <code>email_log.txt</code> for reset link.</div>';
        } else {
            $message = '<div class="alert alert-danger">Email not found.</div>';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Forgot Password</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="card shadow mx-auto" style="max-width:520px;">
    <div class="card-body">
      <h4>Forgot Password</h4>
      <?php echo $message; ?>
      <form method="POST" data-validate>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <button class="btn btn-warning w-100">Send Reset Link</button>
      </form>
      <p class="mt-3"><a href="index.php">Back to Login</a></p>
    </div>
  </div>
</div>
<script src="assets/validation.js"></script>
</body>
</html>
