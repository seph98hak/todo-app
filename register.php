<?php
// register.php
session_start();
require_once 'db.php';
require_once 'utils.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $terms = isset($_POST['terms']);

    if (!$terms) {
        $message = '<div class="alert alert-danger">You must agree to the terms.</div>';
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
        $message = '<div class="alert alert-danger">Username must be 3-50 chars: letters, numbers, underscore.</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="alert alert-danger">Invalid email.</div>';
    } elseif (strlen($password) < 8) {
        $message = '<div class="alert alert-danger">Password must be at least 8 characters.</div>';
    } elseif ($password !== $confirm) {
        $message = '<div class="alert alert-danger">Passwords do not match.</div>';
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        try {
            $stmt->execute([$username, $email, $hashed]);
            $message = '<div class="alert alert-success">Registered successfully! <a href="index.php">Login now</a></div>';
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Username or email already taken.</div>';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Register - TODO App</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="card shadow mx-auto" style="max-width:600px;">
    <div class="card-body">
      <h3 class="text-center mb-3">Register</h3>
      <?php echo $message; ?>
      <form method="POST" data-validate>
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" pattern="[A-Za-z0-9_]{3,50}" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" minlength="8" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" minlength="8" required>
          </div>
        </div>
        <div class="form-check mb-3">
          <input type="checkbox" name="terms" class="form-check-input" id="terms" required>
          <label for="terms" class="form-check-label">I agree to the terms</label>
        </div>
        <button class="btn btn-primary w-100">Register</button>
      </form>
      <p class="text-center mt-3"><a href="index.php">Already have an account? Login</a></p>
    </div>
  </div>
</div>
<script src="assets/validation.js"></script>
</body>
</html>
