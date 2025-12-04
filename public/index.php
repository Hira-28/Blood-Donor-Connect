<?php
require_once 'db.php'; 

$blood = isset($_GET['blood_group']) ? trim($_GET['blood_group']) : '';
$city  = isset($_GET['city']) ? trim($_GET['city']) : '';

$sql = "SELECT * FROM donors WHERE 1=1";
$params = array();
if ($blood !== '') { $sql .= " AND blood_group = :blood"; $params[':blood'] = $blood; }
if ($city !== '')  { $cityEsc = str_replace(array('%','_'), array('\%','\_'), $city); $sql .= " AND city LIKE :city"; $params[':city'] = "%".$cityEsc."%"; }
$sql .= " ORDER BY (availability = 'available') DESC, created_at DESC LIMIT 200";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$donors = $stmt->fetchAll(PDO::FETCH_ASSOC);

$availStmt = $pdo->prepare("SELECT id, name, city, blood_group FROM donors WHERE availability = 'available' ORDER BY created_at DESC LIMIT 10");
$availStmt->execute();
$availableDonors = $availStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Blood Donor Dashboard</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>

:root {
  --bg: #f6f7fb;
  --surface: #ffffff;
  --muted: #6b7280;

  --accent: #b80000;
  --accent-dark: #8a0000;
  --accent-soft: rgba(184, 0, 0, 0.08);

  --card-radius: 12px;
  --sidebar-width: 260px;
}

* { box-sizing: border-box; }

body {
  margin: 0;
  font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial;
  background: var(--bg);
  color: #0b1220;
}

a {
  color: inherit;
  text-decoration: none;
}
#sidebar {
  position: fixed;
  left: 0;
  top: 0;
  bottom: 0;

  width: var(--sidebar-width);
  padding: 20px;

  background: linear-gradient(180deg, var(--accent-dark), var(--accent));
  color: #fff;

  box-shadow: 2px 0 18px rgba(0, 0, 0, 0.08);
  z-index: 100;
}

.brand {
  font-weight: 800;
  font-size: 22px;
  text-align: center;
  margin-bottom: 22px;
}

.nav-link {
  display: block;
  padding: 12px 16px;
  margin-bottom: 8px;

  border-radius: 10px;
  color: #fff;
  font-weight: 500;

  transition: 0.2s ease;
}

.nav-link:hover {
  background: rgba(255, 255, 255, 0.12);
}

.main {
  margin-left: var(--sidebar-width);
  padding: 28px;
  transition: 0.2s ease;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
}

.card {
  background: var(--surface);
  border-radius: var(--card-radius);
  padding: 16px;

  box-shadow: 0 8px 30px rgba(3, 8, 18, 0.06);
}

.top-box {
  background: var(--accent-soft);
  border-left: 6px solid var(--accent);
  padding: 14px;
  border-radius: 10px;
  margin-bottom: 16px;
}

.search-row .input {
  width: 100%;
  padding: 10px 12px;
  border-radius: 10px;
  border: 1px solid #e6e9f2;
  background: #fff;
}

.donor-card {
  display: flex;
  justify-content: space-between;
  align-items: center;

  padding: 14px;
  border-radius: 10px;
  margin-bottom: 12px;

  background: var(--surface);
  box-shadow: 0 2px 14px rgba(3, 8, 18, 0.05);
}

.donor-meta {
  max-width: 68%;
}

.muted {
  color: var(--muted);
  font-size: 13px;
}

.badge-available {
  background: #16a34a;
  color: #fff;
  padding: 6px 10px;
  border-radius: 8px;
  font-weight: 700;
}

.badge-unavailable {
  background: #b91c1c;
  color: #fff;
  padding: 6px 10px;
  border-radius: 8px;
  font-weight: 700;
}

.available-section {
  clear: both;
  margin-top: 30px;
  width: 100%;
}

.available-section .card {
  width: 100%;
}

/* list style */
.list-item {
  display: flex;
  justify-content: space-between;
  align-items: center;

  padding: 12px;
  border-radius: 10px;
  background: var(--surface);

  margin-bottom: 10px;
  box-shadow: 0 2px 12px rgba(3, 8, 18, 0.04);
}

.btn {
  display: inline-block;
  padding: 8px 12px;
  border-radius: 8px;
  border: 0;
  cursor: pointer;
  font-weight: 600;
}

.btn-outline {
  background: transparent;
  border: 1px solid rgba(11, 18, 32, 0.08);
  color: #0b1220;
}

.btn-accent {
  background: var(--accent);
  color: #fff;
}

@media (max-width: 1000px) {
  #sidebar {
    display: none;
  }

  .main {
    margin-left: 0;
    padding: 18px;
  }

  .available-section {
    margin-top: 20px;
  }
}

@media (max-width: 600px) {
  .donor-card {
    flex-direction: column;
    align-items: flex-start;
  }

  .donor-meta {
    max-width: 100%;
    margin-bottom: 10px;
  }
}
</style>
</head>
<body>

<div id="sidebar">
  <div class="brand">❤️ Blood Connect</div>
  <a class="nav-link" href="index.php">🔍 Dashboard</a>
  <a class="nav-link" href="top_donors.php">🏆 Top Donors</a>
  <a class="nav-link" href="donors.php">📋 All Donors</a>
  <a class="nav-link" href="register.php">➕ Register Donor</a>
  <a class="nav-link" href="donor_login.php">🔐 Donor Portal</a>

  <div style="margin-top:18px;font-size:13px;color:rgba(255,255,255,0.9)">A drop for you, a life for someone ❤️</div>
</div>

<div class="main">
  <div class="header">
    <div>
      <h2 style="margin:0">Blood Donor Dashboard</h2>
      <div class="muted">Find donors fast — view profiles. Updates by donors only.</div>
    </div>
    <div style="display:flex;gap:10px">
      <a class="btn btn-outline" href="register.php">Register</a>
      <a class="btn btn-accent" href="top_donors.php">Top Donors</a>
    </div>
  </div>

  <div class="top-box card">
    <strong>🏆 Top Donors</strong> — inspiring donors to give more. <a href="top_donors.php" style="margin-left:10px;font-weight:700;color:#4b0000">View →</a>
  </div>

  <div class="card search-row" style="margin-bottom:14px">
    <form method="get" class="row" style="display:flex;gap:12px;flex-wrap:wrap">
      <div style="flex:0 0 220px">
        <label class="muted">Blood group</label>
        <select name="blood_group" class="input" style="width:100%;padding:8px;border-radius:8px">
          <option value="">All blood groups</option>
          <?php foreach(['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $g){ $sel = ($g === $blood) ? 'selected' : ''; echo "<option value=\"$g\" $sel>$g</option>"; } ?>
        </select>
      </div>
      <div style="flex:1">
        <label class="muted">City</label>
        <input type="text" name="city" class="input" placeholder="City (e.g. Dhaka)" value="<?php echo htmlspecialchars($city); ?>">
      </div>
      <div style="display:flex;align-items:end;gap:8px">
        <button class="btn btn-accent" type="submit">Search</button>
        <a class="btn btn-outline" href="donors.php">See All Donors</a>
      </div>
    </form>
  </div>

<div class="donor-list-container">

  <?php if (empty($donors)): ?>
    <div class="card">No donors found. Try different filters.</div>

  <?php else: foreach($donors as $d):

      $raw = strtolower(trim($d['availability'] ?? ''));
      $available_values = ['yes', 'available', '1', 'true', 'y'];
      $avail = in_array($raw, $available_values) ? 'available' : 'unavailable';

      $last = (!empty($d['last_donation_date']) && $d['last_donation_date'] !== '0000-00-00')
              ? $d['last_donation_date']
              : '—';
  ?>
    <div class="donor-card">
      <div class="donor-meta">
        <div style="font-weight:800">
          <?php echo htmlspecialchars($d['name']); ?>
          <span style="color:var(--accent);font-weight:700">
            · <?php echo htmlspecialchars($d['blood_group']); ?>
          </span>
        </div>

        <div class="muted">
          <?php echo htmlspecialchars($d['city']); ?>
          <?php if(!empty($d['area'])) echo ' · '.htmlspecialchars($d['area']); ?>
        </div>

        <div class="muted" style="margin-top:6px">
          Last donation: <?php echo $last; ?>
        </div>
      </div>

      <div style="text-align:right">
        <div style="margin-bottom:8px">
          <span class="<?php echo ($avail === 'available') ? 'badge-available' : 'badge-unavailable'; ?>">
            <?php echo ucfirst($avail); ?>
          </span>
        </div>

        <div style="display:flex;gap:8px;justify-content:flex-end">
          <a class="btn btn-outline" href="tel:<?php echo htmlspecialchars($d['phone']); ?>">Call</a>
          <a class="btn btn-outline" href="profile.php?id=<?php echo urlencode($d['id']); ?>">Profile</a>
        </div>
      </div>
    </div>

  <?php endforeach; endif; ?>
  <div class="available-section">
    <div class="card" style="margin-top:20px;">
      <h4 style="margin:0 0 8px 0;">Available Donors</h4>

      <?php if (empty($availableDonors)): ?>
        <div class="muted">No available donors right now.</div>

      <?php else: foreach ($availableDonors as $ad): ?>
        <div class="list-item">
          <div>
            <strong><?php echo htmlspecialchars($ad['name']); ?></strong>
            <div class="muted">
              <?php echo htmlspecialchars($ad['city']); ?> · <?php echo htmlspecialchars($ad['blood_group']); ?>
            </div>
          </div>

          <div>
            <a class="btn btn-outline" href="profile.php?id=<?php echo urlencode($ad['id']); ?>" style="padding:6px 8px;">Profile</a>
          </div>
        </div>
      <?php endforeach; endif; ?>

    </div>
  </div>

</div> 
</body>
</html>
