<?php
require_once 'db.php';
$stmt = $pdo->query("SELECT * FROM donors ORDER BY created_at DESC");
$donors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html><html><head><meta charset="utf-8"><title>All Donors</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{
  padding:18px;
  background:#f6f7fb;
  font-family:Inter,system-ui
  }
</style>
</head><body>
<div class="container">
  <h3>All Donors</h3>
  <div class="row g-3">
    <?php foreach($donors as $d): ?>
      <div class="col-md-6">
        <div class="card p-3">
          <div class="d-flex justify-content-between">
            <div>
              <strong><?php echo htmlspecialchars($d['name']); ?></strong><br>
              <small class="text-muted"><?php echo htmlspecialchars($d['city']); ?> · <?php echo htmlspecialchars($d['blood_group']); ?></small>
            </div>
            <div>
              <a class="btn btn-sm btn-outline-primary" href="profile.php?id=<?php echo $d['id']; ?>">Profile</a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
</body></html>
