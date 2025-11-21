<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once "db.php";
require_once "utils.php";

$user_id = $_SESSION['user_id'];

/* ============================
   ADD / EDIT TASK
============================ */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";
    $title = trim($_POST["title"] ?? "");
    $desc = trim($_POST["description"] ?? "");
    $status = $_POST["status"] ?? "pending";
    $priority = $_POST["priority"] ?? "medium";
    $notifications = isset($_POST["notifications"]) ? 1 : 0;
    $due_date = $_POST["due_date"] ?: null;
    $category_id = !empty($_POST["category_id"]) ? (int)$_POST["category_id"] : null;

    if ($title === "") {
        $_SESSION["flash"] = ["type" => "danger", "msg" => "Title is required."];
        header("Location: todos.php");
        exit;
    }

    /* File upload */
    $attachment = "";
    if (!empty($_FILES["attachment"]["name"]) && $_FILES["attachment"]["error"] === 0) {

        $file = $_FILES["attachment"];
        $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $allowed = ["pdf", "jpg", "jpeg", "png"];

        if (in_array($ext, $allowed) && $file["size"] <= 5 * 1024 * 1024) {
            $newFile = "uploads/" . time() . "_" . preg_replace("/[^A-Za-z0-9_.-]/", "_", $file["name"]);
            move_uploaded_file($file["tmp_name"], $newFile);
            $attachment = $newFile;
        }
    }

    if ($action === "add") {

        $stmt = $pdo->prepare("INSERT INTO todos 
            (user_id, category_id, title, description, status, priority, notifications, due_date, attachment)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $user_id, $category_id, $title, $desc, $status,
            $priority, $notifications, $due_date, $attachment
        ]);

        $_SESSION["flash"] = ["type" => "success", "msg" => "Task added successfully."];

    } elseif ($action === "edit") {

        $id = (int)$_POST["id"];

        $getOld = $pdo->prepare("SELECT * FROM todos WHERE id = ? AND user_id = ?");
        $getOld->execute([$id, $user_id]);
        $old = $getOld->fetch();

        if ($attachment === "") {
            $attachment = $old["attachment"];
        }

        if ($status === "completed" && $old["status"] !== "completed" && $notifications) {

            $emailQuery = $pdo->prepare("SELECT email FROM users WHERE id = ?");
            $emailQuery->execute([$user_id]);
            $email = $emailQuery->fetchColumn();

            simulate_email($email, "Task Completed", "Your task '$title' has been completed.");
        }

        $stmt = $pdo->prepare("UPDATE todos SET 
            category_id = ?, title = ?, description = ?, status = ?, priority = ?, notifications = ?, 
            due_date = ?, attachment = ?
            WHERE id = ? AND user_id = ?");

        $stmt->execute([
            $category_id, $title, $desc, $status, $priority,
            $notifications, $due_date, $attachment, $id, $user_id
        ]);

        $_SESSION["flash"] = ["type" => "success", "msg" => "Task updated successfully."];
    }

    header("Location: todos.php");
    exit;
}

/* ============================
   DELETE TASK
============================ */
if (isset($_GET["delete"])) {

    $id = (int)$_GET["delete"];

    $stmt = $pdo->prepare("DELETE FROM todos WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);

    $_SESSION["flash"] = ["type" => "success", "msg" => "Task deleted."];

    header("Location: todos.php");
    exit;
}

/* ============================
   FILTERS + PAGINATION
============================ */
$page = max(1, (int)($_GET["page"] ?? 1));
$limit = 6;
$offset = ($page - 1) * $limit;

$where = "WHERE t.user_id = ?";
$params = [$user_id];

// Search filter
if (!empty($_GET["search"])) {
    $s = "%" . $_GET["search"] . "%";
    $where .= " AND (t.title LIKE ? OR t.description LIKE ?)";
    $params[] = $s;
    $params[] = $s;
}

// Status filter
if (!empty($_GET["status"])) {
    $where .= " AND t.status = ?";
    $params[] = $_GET["status"];
}

// Category filter
if (!empty($_GET["category"])) {
    $where .= " AND t.category_id = ?";
    $params[] = (int)$_GET["category"];
}

/* Count total rows */
$count = $pdo->prepare("SELECT COUNT(*) FROM todos t $where");
$count->execute($params);
$total = $count->fetchColumn();
$pages = max(1, ceil($total / $limit));

/* ============================
   FETCH TASKS (100% FIXED)
============================ */

$sql = "SELECT t.*, c.name AS cat_name
        FROM todos t
        LEFT JOIN categories c ON t.category_id = c.id
        $where
        ORDER BY t.created_at DESC
        LIMIT ? OFFSET ?";

$stmt = $pdo->prepare($sql);

/* bind query parameters */
$i = 1;
foreach ($params as $p) {
    $stmt->bindValue($i++, $p);
}

/* bind LIMIT and OFFSET correctly as int */
$stmt->bindValue($i++, (int)$limit, PDO::PARAM_INT);
$stmt->bindValue($i++, (int)$offset, PDO::PARAM_INT);

$stmt->execute();
$todos = $stmt->fetchAll();

/* Category list */
$cq = $pdo->prepare("SELECT id, name FROM categories WHERE user_id = ? ORDER BY name");
$cq->execute([$user_id]);
$categories = $cq->fetchAll();

$flash = $_SESSION["flash"] ?? null;
unset($_SESSION["flash"]);
?>
<!doctype html>
<html lang="en">

<head>
<meta charset="utf-8">
<title>TODOs</title>
<link rel="stylesheet" href="assets/theme.css">

</head>

<body>
<?php include "navbar.php"; ?>

<div class="container mt-4">

<?php if ($flash): ?>
<div class="alert alert-<?php echo $flash["type"]; ?>">
    <?php echo $flash["msg"]; ?>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between mb-3">
    <h3>Your Tasks</h3>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">+ Add Task</button>
</div>

<!-- FILTERS -->
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4">
        <input class="form-control" name="search" placeholder="Search..." value="<?php echo e($_GET["search"] ?? ""); ?>">
    </div>

    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="">All Status</option>
            <option value="pending" <?php echo (($_GET["status"] ?? "") === "pending") ? "selected" : ""; ?>>Pending</option>
            <option value="in_progress" <?php echo (($_GET["status"] ?? "") === "in_progress") ? "selected" : ""; ?>>In Progress</option>
            <option value="completed" <?php echo (($_GET["status"] ?? "") === "completed") ? "selected" : ""; ?>>Completed</option>
        </select>
    </div>

    <div class="col-md-3">
        <select name="category" class="form-select">
            <option value="">All Categories</option>
            <?php foreach ($categories as $c): ?>
            <option value="<?php echo $c["id"]; ?>"
                <?php echo (($_GET["category"] ?? "") == $c["id"]) ? "selected" : ""; ?>>
                <?php echo e($c["name"]); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-2">
        <button class="btn btn-primary w-100">Filter</button>
    </div>
</form>

<!-- LIST TABLE -->
<div class="table-responsive">
<table class="table table-bordered table-striped">
<thead class="table-primary">
<tr>
    <th>Title</th>
    <th>Status</th>
    <th>Priority</th>
    <th>Due</th>
    <th>Category</th>
    <th>File</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>

<?php foreach ($todos as $t): ?>
<tr>
    <td><?php echo e($t["title"]); ?></td>
    <td><?php echo e($t["status"]); ?></td>
    <td><?php echo e($t["priority"]); ?></td>
    <td><?php echo e($t["due_date"] ?: "—"); ?></td>
    <td><?php echo e($t["cat_name"] ?: "—"); ?></td>
    <td><?php echo $t["attachment"] ? '<a href="'.e($t["attachment"]).'" target="_blank">View</a>' : "—"; ?></td>

    <td>
        <button class="btn btn-sm btn-primary"
            onclick='openEdit(<?php echo json_encode($t); ?>)'
            data-bs-toggle="modal" data-bs-target="#editModal">Edit</button>

        <a href="?delete=<?php echo $t["id"]; ?>" 
           onclick="return confirm('Delete this task?')"
           class="btn btn-sm btn-danger">Delete</a>
    </td>
</tr>
<?php endforeach; ?>

</tbody>
</table>
</div>

<!-- PAGINATION -->
<nav>
<ul class="pagination">
<?php for ($i = 1; $i <= $pages; $i++): ?>
<li class="page-item <?php echo ($i == $page ? "active" : ""); ?>">
    <a class="page-link" href="?<?php
        $q = $_GET;
        $q["page"] = $i;
        echo http_build_query($q);
    ?>"><?php echo $i; ?></a>
</li>
<?php endfor; ?>
</ul>
</nav>

</div>

<!-- ADD MODAL -->
<div class="modal fade" id="addModal">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="add">
    <?php include "todo_form.php"; ?>
    <div class="modal-footer">
        <button class="btn btn-success">Save</button>
    </div>
</form>
</div>
</div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="edit">
    <input type="hidden" name="id" id="edit_id">
    <?php include "todo_form.php"; ?>
    <div class="modal-footer">
        <button class="btn btn-primary">Update</button>
    </div>
</form>
</div>
</div>
</div>

<script>
function openEdit(t) {
    let m = document.getElementById("editModal");
    m.querySelector("#edit_id").value = t.id;
    m.querySelector('[name="title"]').value = t.title;
    m.querySelector('[name="description"]').value = t.description;
    m.querySelector('[name="status"]').value = t.status;

    m.querySelectorAll('[name="priority"]').forEach(r => {
        r.checked = (r.value === t.priority);
    });

    m.querySelector('[name="notifications"]').checked = (t.notifications == 1);
    m.querySelector('[name="due_date"]').value = t.due_date ?? "";
    m.querySelector('[name="category_id"]').value = t.category_id ?? "";
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
