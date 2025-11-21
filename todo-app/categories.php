<?php
// categories.php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }
require_once 'db.php';
require_once 'utils.php';

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(trim($_POST['name'] ?? ''))) {
    $name = trim($_POST['name']);
    $stmt = $pdo->prepare("INSERT INTO categories (name, user_id) VALUES (?, ?)");
    $stmt->execute([$name, $user_id]);
    header('Location: categories.php');
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    header('Location: categories.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM categories WHERE user_id = ? ORDER BY name");
$stmt->execute([$user_id]);
$categories = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Categories - TODO App</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
  <h3>Manage Categories</h3>
  <form method="POST" class="row g-3 mb-4">
    <div class="col-auto flex-grow-1">
      <input type="text" name="name" class="form-control" placeholder="New category" required>
    </div>
    <div class="col-auto">
      <button class="btn btn-success">Add</button>
    </div>
  </form>

  <div class="table-responsive">
  <table class="table table-bordered">
    <thead class="table-primary">
      <tr><th>#</th><th>Name</th><th>Action</th></tr>
    </thead>
    <tbody>
      <?php foreach ($categories as $i => $cat): ?>
      <tr>
        <td><?php echo $i+1; ?></td>
        <td><?php echo e($cat['name']); ?></td>
        <td>
          <a href="?delete=<?php echo $cat['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete category?')">Delete</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
</body>
</html>
