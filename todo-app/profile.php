<?php
// profile.php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }
require_once 'db.php';
require_once 'utils.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check if you add token to form later (omitted to keep simple)
    $bio = trim($_POST['bio'] ?? '');
    $profile_pic = $user['profile_pic'] ?? 'uploads/default.jpg';

    if (!empty($_FILES['profile_pic']['name']) && $_FILES['profile_pic']['error'] == 0) {
        $file = $_FILES['profile_pic'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png'];
        if (in_array($ext, $allowed) && $file['size'] < 2 * 1024 * 1024) {
            $newName = 'uploads/' . time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file['name']);
            if (move_uploaded_file($file['tmp_name'], $newName)) {
                $profile_pic = $newName;
            }
        } else {
            $message = '<div class="alert alert-danger">Invalid image (JPG/PNG, <2MB)</div>';
        }
    }

    $update = $pdo->prepare("UPDATE users SET bio = ?, profile_pic = ? WHERE id = ?");
    $update->execute([$bio, $profile_pic, $user_id]);
    header('Location: profile.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Profile - TODO App</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>.profile-pic{width:180px;height:180px;object-fit:cover;border-radius:50%;}</style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
  <div class="row">
    <div class="col-md-4 text-center">
      <img src="<?php echo e($user['profile_pic'] ?: 'uploads/default.jpg'); ?>" alt="Profile" class="profile-pic img-thumbnail">
      <h4 class="mt-2"><?php echo e($user['username']); ?></h4>
    </div>
    <div class="col-md-8">
      <?php echo $message; ?>
      <div class="card shadow-sm">
        <div class="card-header"><h5>About Me</h5></div>
        <div class="card-body"><p><?php echo nl2br(e($user['bio'] ?: 'No bio.')); ?></p></div>
      </div>

      <div class="card mt-4 shadow-sm">
        <div class="card-header"><h5>Update Profile</h5></div>
        <div class="card-body">
          <form method="POST" enctype="multipart/form-data" data-validate>
            <div class="mb-3">
              <label class="form-label">Bio</label>
              <textarea name="bio" class="form-control" rows="4"><?php echo e($user['bio']); ?></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Profile Picture (JPG/PNG, &lt;2MB)</label>
              <input type="file" name="profile_pic" class="form-control" accept=".jpg,.jpeg,.png">
            </div>
            <button class="btn btn-primary">Save Changes</button>
          </form>
        </div>
      </div>

    </div>
  </div>
</div>
<script src="assets/validation.js"></script>
</body>
</html>
