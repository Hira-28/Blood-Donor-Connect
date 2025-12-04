<?php
require_once 'db.php';

$id = (int)($_GET['id'] ?? 0);
if(!$id) die('Donor id required');

$stmt = $pdo->prepare("SELECT * FROM donors WHERE id = ?");
$stmt->execute([$id]);
$d = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$d) die('Donor not found');

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['donated_on'], $_POST['units'])){
    $donated_on = $_POST['donated_on'];
    $units = (int)$_POST['units'];

    if($units > 0 && $donated_on){
        $stmt = $pdo->prepare("INSERT INTO donations (donor_id, donated_on, units) VALUES (?, ?, ?)");
        $stmt->execute([$id, $donated_on, $units]);

        $up = $pdo->prepare("UPDATE donors SET last_donation_date = ? WHERE id = ?");
        $up->execute([$donated_on, $id]);

        header("Location: profile.php?id=$id");
        exit;
    } else {
        $error = "Please enter valid donation info.";
    }
}

$hasDonations = false;
try {
    $check = $pdo->query("SHOW TABLES LIKE 'donations'")->fetch();
    if($check) $hasDonations = true;
} catch(Exception $e){ $hasDonations = false; }

$rows = [];
$totalUnits = 0;
if($hasDonations){
    $q = $pdo->prepare("SELECT donated_on, units FROM donations WHERE donor_id = ? ORDER BY donated_on DESC");
    $q->execute([$id]);
    $rows = $q->fetchAll(PDO::FETCH_ASSOC);

    $sum = $pdo->prepare("SELECT SUM(units) FROM donations WHERE donor_id = ?");
    $sum->execute([$id]);
    $totalUnits = (int)$sum->fetchColumn();
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo htmlspecialchars($d['name']); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{font-family:Inter,system-ui;background:#f6f7fb;padding:18px}

.card-box{
    background:#fff;
    border-radius:12px;
    padding:18px;
    box-shadow:0 4px 20px rgba(0,0,0,0.06);
}
.history-item{
    padding:10px 12px;
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius:8px;
    margin-bottom:8px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.badge-unit{
    background:#b80000;
    color:#fff;
    padding:4px 10px;
    border-radius:6px;
    font-weight:700;
}
</style>
</head>

<body>

<div class="container">

  <div class="mb-3">
    <a href="index.php" class="btn btn-secondary">&larr; Back to Dashboard</a>
  </div>

  <div class="card-box mb-3">
    <div class="d-flex justify-content-between">
      <div>
        <h4>
            <?php echo htmlspecialchars($d['name']); ?>
            <small class="text-danger">· <?php echo htmlspecialchars($d['blood_group']); ?></small>
        </h4>
        <div class="text-muted">
            <?php echo htmlspecialchars($d['city']); ?>
            <?php if($d['area']) echo " · ".htmlspecialchars($d['area']); ?>
        </div>
        <div style="margin-top:8px">
            Phone:
            <a href="tel:<?php echo htmlspecialchars($d['phone']); ?>">
                <?php echo htmlspecialchars($d['phone']); ?>
            </a>
        </div>
        <div style="margin-top:6px">
            Last donation:
            <strong><?php echo $d['last_donation_date'] ?: '—'; ?></strong>
        </div>
        <div style="margin-top:6px">
            Total units donated:
            <strong><?php echo $totalUnits; ?> unit(s)</strong>
        </div>
      </div>
      </div>
    </div>
  <?php if($hasDonations): ?>
  <div class="card-box ">

      <h5 style="margin-bottom:15px;">Donation History</h5>

      <?php if(empty($rows)): ?>
          <div class="text-muted">No donation records yet.</div>

      <?php else: ?>

          <?php foreach($rows as $r): ?>
              <div class="history-item">
                  <div><strong><?php echo htmlspecialchars($r['donated_on']); ?></strong></div>
                  <div class="badge-unit"><?php echo (int)$r['units']; ?> unit(s)</div>
              </div>
          <?php endforeach; ?>

      <?php endif; ?>

  </div>
  <?php endif; ?>

</div>
 </div>
</body>
</html>
