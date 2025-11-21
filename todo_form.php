<?php
// todo_form.php - expects $pdo and $user_id defined in including file
$catsStmt = $pdo->prepare("SELECT id, name FROM categories WHERE user_id = ? ORDER BY name");
$catsStmt->execute([$user_id]);
$catsList = $catsStmt->fetchAll();
?>
<div class="modal-header">
    <h5 class="modal-title">Task</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <div class="mb-3">
        <label>Title *</label>
        <input type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Description</label>
        <textarea name="description" class="form-control" rows="3"></textarea>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label>Status</label>
            <select name="status" class="form-select">
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label>Priority</label><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="priority" value="low">
                <label class="form-check-label">Low</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="priority" value="medium" checked>
                <label class="form-check-label">Medium</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="priority" value="high">
                <label class="form-check-label">High</label>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <div class="form-check">
            <input type="checkbox" name="notifications" class="form-check-input" id="notifCheck">
            <label class="form-check-label" for="notifCheck">Email when done</label>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label>Due Date</label>
            <input type="date" name="due_date" class="form-control">
        </div>
        <div class="col-md-6 mb-3">
            <label>Category</label>
            <select name="category_id" class="form-select">
                <option value="">None</option>
                <?php foreach ($catsList as $c): ?>
                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="mb-3">
        <label>Attachment (pdf, jpg, png up to 5MB)</label>
        <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
    </div>
</div>
