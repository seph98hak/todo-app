<?php
// index.php - Login
session_start();
require_once 'db.php';
require_once 'utils.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (filter_var($email, FILTER_VALIDATE_EMAIL) && $password !== '') {
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            // Regenerate session id to prevent fixation
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            header("Location: dashboard.php");
            exit;
        } else {
            $message = '<div class="alert alert-danger">Invalid email or password!</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Enter a valid email and password.</div>';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Login - TODO App</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="assets/theme.css">

  <style>
    body.dark-mode { background:#121212;color:#ddd; }
    .card.dark-mode { background:#1e1e1e; color:#ddd; }
  </style>
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="card shadow mx-auto" style="max-width:480px;">
    <div class="card-body">
      <h3 class="text-center mb-3">Login</h3>
      <?php echo $message; ?>
      <form method="POST" data-validate>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-success w-100">Login</button>
      </form>
      <p class="text-center mt-3">
        <a href="register.php">Register</a> |
        <a href="forgot_password.php">Forgot Password?</a>
      </p>
    </div>
  </div>
</div>
<script src="assets/validation.js"></script>
</body>
</html>
