<?php
// donor_portal.php
session_start();
require_once 'db.php';

if (!isset($_SESSION['donor_id'])) {
    header('Location: donor_login.php');
    exit;
}

$donor_id = (int)$_SESSION['donor_id'];
$msg = '';
$err = '';

$stmt = $pdo->prepare("SELECT * FROM donors WHERE id = ?");
$stmt->execute([$donor_id]);
$donor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$donor) {
    session_unset();
    session_destroy();
    header('Location: donor_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['update_availability'])) {

        $raw = strtolower(trim($_POST['availability'] ?? ''));

        $new = ($raw === 'available') ? 'available' : 'not available';

        $u = $pdo->prepare("UPDATE donors SET availability = ? WHERE id = ?");
        $u->execute([$new, $donor_id]);

        $donor['availability'] = $new;
        $msg = "Availability updated.";
    }

    if (isset($_POST['add_donation'])) {
        $donated_on = trim($_POST['donated_on'] ?? '');
        $units = (int)($_POST['units'] ?? 1);

        if (!$donated_on) {
            $err = 'Please choose a donation date.';
        } else {
            $ins = $pdo->prepare("INSERT INTO donations (donor_id, donated_on, units) VALUES (?, ?, ?)");
            $ins->execute([$donor_id, $donated_on, max(1, $units)]);
            $upd = $pdo->prepare("UPDATE donors SET last_donation_date = ? WHERE id = ?");
            $upd->execute([$donated_on, $donor_id]);

            $donor['last_donation_date'] = $donated_on;
            $msg = "Donation recorded successfully.";
        }
    }
    $stmt = $pdo->prepare("SELECT * FROM donors WHERE id = ?");
    $stmt->execute([$donor_id]);
    $donor = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Donor Portal</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body {
      background:#f6f7fb;
      font-family:Inter, Arial, sans-serif;
      padding:20px;
  }
  .pill {
      border-radius:50px;
      padding:6px 14px;
      font-weight:600;
      font-size:.9rem;
      display:inline-block;
  }
</style>
</head>
<body>

<div class="container" style="max-width:750px;">
  <div class="card p-4 shadow-sm">

    <div class="d-flex justify-content-between align-items-start">
      <div>
        <h4 class="mb-0">Welcome, <?php echo htmlspecialchars($_SESSION['donor_name'] ?? $donor['name']); ?></h4>
        <div class="text-muted">Manage your availability and donation history.</div>
      </div>
      <a href="logout.php?from=donor_portal" class="btn btn-outline-secondary">Logout</a>
    </div>

    <?php if($msg): ?>
      <div class="alert alert-success mt-3"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <?php if($err): ?>
      <div class="alert alert-danger mt-3"><?php echo htmlspecialchars($err); ?></div>
    <?php endif; ?>

    <div class="mt-4">
      <h5>Availability</h5>

      <?php
        $current = strtolower($donor['availability']);
        $isAvailable = ($current === 'available');
        $cls = $isAvailable ? 'bg-success text-white' : 'bg-danger text-white';
        $label = $isAvailable ? 'Available' : 'Not available';
      ?>

      <div class="mb-2">
        <span class="pill <?php echo $cls; ?>"><?php echo $label; ?></span>
      </div>

      <form method="post" class="d-flex align-items-center gap-2">
        <select name="availability" class="form-select" style="width:220px">
          <option value="available" <?php echo $isAvailable ? 'selected' : ''; ?>>Available</option>
          <option value="not available" <?php echo !$isAvailable ? 'selected' : ''; ?>>Not available</option>
        </select>
        <button name="update_availability" class="btn btn-primary">Update</button>
      </form>
    </div>

    <div class="mt-4">
      <h5>Add Donation Record</h5>

      <form method="post" class="row g-2">
        <div class="col-md-6">
          <label class="form-label">Donation Date</label>
          <input type="date" name="donated_on" class="form-control" required>
        </div>

        <div class="col-md-3">
          <label class="form-label">Units</label>
          <input type="number" name="units" value="1" min="1" class="form-control">
        </div>

        <div class="col-md-3 d-flex align-items-end">
          <button name="add_donation" class="btn btn-success w-100">Record</button>
        </div>
      </form>

      <div class="text-muted small mt-2">Donation is stored in <code>donations</code> table.</div>
    </div>

    <div class="mt-4">
      <h5>Your Donation History</h5>

      <?php
        $check = $pdo->query("SHOW TABLES LIKE 'donations'")->fetch();

        if ($check) {
            $q = $pdo->prepare("SELECT donated_on, units FROM donations WHERE donor_id = ? ORDER BY donated_on DESC");
            $q->execute([$donor_id]);
            $rows = $q->fetchAll(PDO::FETCH_ASSOC);

            if (empty($rows)) {
                echo '<div class="text-muted">No donation history found.</div>';
            } else {
                echo '<ul class="list-group">';
                foreach ($rows as $r) {
                    echo '<li class="list-group-item d-flex justify-content-between">
                            <span>' . htmlspecialchars($r['donated_on']) . '</span>
                            <span class="badge bg-primary">' . (int)$r['units'] . ' units</span>
                          </li>';
                }
                echo '</ul>';
            }
        } else {
            echo '<div class="text-muted">Donations table missing.</div>';
        }
      ?>
    </div>

  </div>
</div>

</body>
</html>
