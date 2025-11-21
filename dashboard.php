<?php
// dashboard.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
require_once 'db.php';
require_once 'utils.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$pending = $pdo->prepare("SELECT COUNT(*) FROM todos WHERE user_id = ? AND status = 'pending'");
$pending->execute([$user_id]);
$pending_count = $pending->fetchColumn();

$overdue = $pdo->prepare("SELECT COUNT(*) FROM todos WHERE user_id = ? AND due_date < CURDATE() AND status != 'completed'");
$overdue->execute([$user_id]);
$overdue_count = $overdue->fetchColumn();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Dashboard - TODO App</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .stat-card { min-height:120px; }
    .dark-mode .card { background:#1e1e1e; color:#ddd; }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center">
    <h2>Welcome, <strong><?php echo e($user['username']); ?></strong>!</h2>
    <div>
      <a href="todos.php" class="btn btn-primary">Manage TODOs</a>
    </div>
  </div>
  <div class="row mt-4 g-3">
    <div class="col-md-4">
      <div class="card stat-card shadow-sm">
        <div class="card-body">
          <h6 class="card-title">Pending Tasks</h6>
          <h2><?php echo (int)$pending_count; ?></h2>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card stat-card shadow-sm">
        <div class="card-body">
          <h6 class="card-title">Overdue</h6>
          <h2><?php echo (int)$overdue_count; ?></h2>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card stat-card shadow-sm">
        <div class="card-body">
          <h6 class="card-title">Profile</h6>
          <p class="mb-0">Manage your profile and settings.</p>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
