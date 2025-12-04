<?php
require_once 'db.php';
$rankByDonations = false;
try {
  $check = $pdo->query("SHOW TABLES LIKE 'donations'")->fetch();
  if($check) $rankByDonations = true;
} catch(Exception $e){ $rankByDonations = false; }

$top = [];
if($rankByDonations){
  $q = $pdo->query("SELECT dr.id, dr.name, dr.city, dr.blood_group, COUNT(d.id) AS donations
                    FROM donors dr
                    JOIN donations d ON d.donor_id = dr.id
                    GROUP BY dr.id ORDER BY donations DESC LIMIT 20");
  $top = $q->fetchAll(PDO::FETCH_ASSOC);
} else {
  $q = $pdo->query("SELECT id, name, city, blood_group, last_donation_date as donations FROM donors ORDER BY last_donation_date DESC LIMIT 20");
  $top = $q->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!doctype html>
<html><head>
<meta charset="utf-8"><title>Top Donors</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{font-family:Inter,system-ui;padding:20px;background:#f6f7fb}</style>
</head><body>
<div class="container">
  <h3>🏆 Top Donors</h3>
  <p class="text-muted">Ranking by <?php echo $rankByDonations ? 'number of donations' : 'recent donation date (fallback)'; ?></p>
  <div class="list-group">
    <?php foreach($top as $i=>$d): ?>
      <div class="list-group-item d-flex justify-content-between align-items-center">
        <div>
          <strong><?php echo htmlspecialchars($d['name']); ?></strong>
          <div class="text-muted"><?php echo htmlspecialchars($d['city']); ?> · <?php echo htmlspecialchars($d['blood_group']); ?></div>
        </div>
        <div class="text-end">
          <?php if($rankByDonations): ?>
            <span class="badge bg-danger"><?php echo (int)$d['donations']; ?> donations</span>
          <?php else: ?>
            <small class="text-muted"><?php echo $d['donations'] ? htmlspecialchars($d['donations']) : '—'; ?></small>
          <?php endif; ?>
          <div><a class="btn btn-sm btn-outline-primary mt-1" href="profile.php?id=<?php echo $d['id']; ?>">Profile</a></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
</body></html>
