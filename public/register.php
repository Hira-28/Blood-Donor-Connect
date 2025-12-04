<?php
require_once 'db.php';
$errors = [];
$success = false;
$assigned_id = null;

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $blood = $_POST['blood_group'] ?? '';
    $city = trim($_POST['city'] ?? '');
    $area = trim($_POST['area'] ?? '');
    $last = $_POST['last_donation_date'] ?? null;
    $donor_id_input = trim($_POST['donor_id'] ?? '');
    $password = $_POST['password'] ?? '';

    // basic validations
    if(!$name) $errors[] = "Name is required.";
    if(!$phone) $errors[] = "Phone is required.";
    if(!$blood) $errors[] = "Blood group is required.";
    if(!$city) $errors[] = "City is required.";
    if(!$password) $errors[] = "Password is required.";
    elseif(strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";

    // donor_id if provided: allow letters, numbers, dash/underscore; length 3-50
    if($donor_id_input !== '') {
        if(!preg_match('/^[A-Za-z0-9_-]{3,50}$/', $donor_id_input)) {
            $errors[] = "Donor ID can only contain letters, numbers, dash or underscore (3-50 chars).";
        } else {
            // check uniqueness
            $chk = $pdo->prepare("SELECT id FROM donors WHERE donor_id = ?");
            $chk->execute([$donor_id_input]);
            if($chk->fetch()) {
                $errors[] = "The chosen Donor ID is already taken. Please pick another or leave blank to auto-generate.";
            }
        }
    }

    if(empty($errors)) {
        // hash password securely
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert with given donor_id or NULL
        $stmt = $pdo->prepare("INSERT INTO donors (name, phone, blood_group, city, area, last_donation_date, availability, donor_id, password_hash, created_at)
            VALUES (:name, :phone, :blood, :city, :area, :last, 'available', :donor_id, :password_hash, NOW())");

        $bind_donor_id = $donor_id_input === '' ? null : $donor_id_input;

        $stmt->execute([
            ':name' => $name,
            ':phone' => $phone,
            ':blood' => $blood,
            ':city' => $city,
            ':area' => $area,
            ':last' => $last ?: null,
            ':donor_id' => $bind_donor_id,
            ':password_hash' => $password_hash
        ]);

        // If donor_id was not provided, generate one from inserted id
        if($bind_donor_id === null) {
            $insertedId = $pdo->lastInsertId();
            $generated = 'D' . str_pad($insertedId, 6, '0', STR_PAD_LEFT);

            // attempt safe update (retry a few times in unlikely collision)
            $try = 0;
            $assigned = null;
            while($try < 5) {
                $upd = $pdo->prepare("UPDATE donors SET donor_id = ? WHERE id = ? AND (donor_id IS NULL OR donor_id = '')");
                $upd->execute([$generated, $insertedId]);
                if($upd->rowCount() > 0) {
                    $assigned = $generated;
                    break;
                }
                // collision fallback
                $generated = 'D' . str_pad($insertedId, 6, '0', STR_PAD_LEFT) . '-' . substr(bin2hex(random_bytes(2)),0,3);
                $try++;
            }
            if($assigned === null) {
                // read existing value (maybe filled by race)
                $q = $pdo->prepare("SELECT donor_id FROM donors WHERE id = ?");
                $q->execute([$insertedId]);
                $assigned = $q->fetchColumn();
            }
            $assigned_id = $assigned;
        } else {
            $assigned_id = $bind_donor_id;
        }

        $success = true;
        // clear POST to avoid re-population on refresh (optional)
        $_POST = [];
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Register as Donor</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    /* keep your existing CSS (same as your original) */
    :root{
      --bg:#f5f7fb;
      --card:#ffffff;
      --muted:#6b7280;
      --primary:#5c6bc0;
      --accent:#4caf50;
      --danger:#ef4444;
      --glass: rgba(92,107,192,0.08);
      --radius:12px;
    }
    *{box-sizing:border-box}
    body{
      margin:0;
      font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;
      background:linear-gradient(180deg,var(--bg),#eef2ff);
      color:#0f172a;
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
      padding:28px;
    }
    .container{
      max-width:980px;
      margin:0 auto;
      display:grid;
      grid-template-columns: 1fr 380px;
      gap:24px;
      align-items:start;
    }
    .card{
      background:var(--card);
      border-radius:var(--radius);
      padding:22px;
      box-shadow:0 8px 30px rgba(2,6,23,0.06);
    }
    .header{
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:12px;
      margin-bottom:14px;
    }
    .h1{
      font-size:20px;
      font-weight:700;
      color:#0f172a;
    }
    .actions a.btn{
      display:inline-block;
      padding:8px 12px;
      background:transparent;
      color:var(--primary);
      border-radius:8px;
      border:1px solid rgba(92,107,192,0.12);
      font-weight:600;
      text-decoration:none;
    }

    .form-row{
      display:grid;
      grid-template-columns:repeat(2,1fr);
      gap:12px;
      margin-top:10px;
    }
    .form-row .full { grid-column: 1 / -1; }
    label{
      display:block;
      font-size:13px;
      font-weight:600;
      margin-bottom:6px;
      color:#0f172a;
    }
    .input, select{
      width:100%;
      padding:10px 12px;
      border-radius:10px;
      border:1px solid #e6e9f2;
      background:linear-gradient(180deg, #ffffff, #fbfdff);
      font-size:14px;
      color:#0f172a;
      outline:none;
      transition:box-shadow .15s, border-color .12s;
    }
    .input:focus, select:focus{
      box-shadow:0 6px 20px rgba(92,107,192,0.12);
      border-color:var(--primary);
    }

    .select-wrap{ position:relative; }
    .btn{
      display:inline-block;
      padding:10px 14px;
      background:var(--primary);
      color:#fff;
      border-radius:10px;
      border:none;
      font-weight:700;
      cursor:pointer;
    }
    .btn.secondary{
      background:#fff;
      color:var(--primary);
      border:1px solid rgba(92,107,192,0.12);
    }
    .footer{ margin-top:12px; font-size:13px; color:var(--muted); }

    .info{
      display:flex;
      flex-direction:column;
      gap:12px;
    }
    .info .card{ padding:18px; display:flex; gap:12px; align-items:flex-start; }
    .avatar{
      width:56px;height:56px;border-radius:10px;background:var(--glass);display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--primary);
      font-size:18px;
    }
    .meta h4{ margin:0;font-size:15px;color:#0f172a; }
    .meta p{ margin:4px 0 0 0;color:var(--muted); font-size:13px; }

    .alert{
      padding:12px 14px;border-radius:10px;margin-bottom:10px;font-weight:600;
    }
    .alert.success{ background:rgba(76,175,80,0.12); color:#065f46; border:1px solid rgba(16,185,129,0.08); }
    .alert.error{ background:rgba(239,68,68,0.06); color:var(--danger); border:1px solid rgba(239,68,68,0.08); }

    @media (max-width:900px){
      .container{ grid-template-columns:1fr; padding:18px; }
      .form-row{ grid-template-columns:1fr; }
    }
  </style>
</head>
<body>
<div class="container">
  <div class="card">
    <div class="header">
      <div class="h1">Register as Donor</div>
      <div class="actions">
        <a href="index.php" class="btn secondary">Search Donors</a>
      </div>
    </div>

    <?php if($success): ?>
      <div class="alert success">
        Thank you — your info is saved.
        <?php if($assigned_id): ?>
          <div style="margin-top:6px;font-weight:700">Your Donor ID: <?php echo htmlspecialchars($assigned_id); ?></div>
          <div style="font-size:13px;color:#334155">Use this Donor ID and the password you set to log in to the donor portal.</div>
        <?php else: ?>
          <div style="margin-top:6px;font-size:13px;color:#334155">You can view donors <a href="donors.php" style="color:inherit;text-decoration:underline">here</a>.</div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if($errors): ?>
      <div class="alert error">
        <?php echo htmlspecialchars(implode(' • ', $errors)); ?>
      </div>
    <?php endif; ?>

    <form method="post" class="form-row" style="margin-top:12px" autocomplete="off" novalidate>
      <div>
        <label for="name">Full name</label>
        <input id="name" class="input" name="name" placeholder="Full name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
      </div>

      <div>
        <label for="phone">Phone</label>
        <input id="phone" class="input" name="phone" placeholder="Phone (e.g. 017xxxxxxxx)" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
      </div>

      <div>
        <label for="blood_group">Blood group</label>
        <div class="select-wrap">
          <select id="blood_group" name="blood_group" class="input">
            <option value="">Select blood group</option>
            <?php foreach(['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $g):
              $sel = (($_POST['blood_group'] ?? '') === $g) ? 'selected' : '';
              echo "<option value=\"$g\" $sel>$g</option>";
            endforeach; ?>
          </select>
        </div>
      </div>

      <div>
        <label for="city">City</label>
        <input id="city" class="input" name="city" placeholder="City" value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>">
      </div>

      <div class="full">
        <label for="donor_id">Donor ID (optional)</label>
        <input id="donor_id" class="input" name="donor_id" placeholder="Choose an ID (e.g. Hira123) or leave blank to auto-generate" value="<?php echo htmlspecialchars($_POST['donor_id'] ?? ''); ?>">
      </div>

      <div>
        <label for="password">Password</label>
        <input id="password" class="input" name="password" placeholder="Password (min 6 chars)" type="password" value="">
      </div>

      <div class="full">
        <label for="area">Area (optional)</label>
        <input id="area" class="input" name="area" placeholder="Area (optional)" value="<?php echo htmlspecialchars($_POST['area'] ?? ''); ?>">
      </div>

      <div class="full">
        <label for="last_donation_date">Last donation (optional)</label>
        <input id="last_donation_date" class="input" type="date" name="last_donation_date" value="<?php echo htmlspecialchars($_POST['last_donation_date'] ?? ''); ?>">
      </div>

      <div style="grid-column:1 / -1; display:flex; justify-content:flex-end; margin-top:6px">
        <button class="btn" type="submit">Register</button>
      </div>
    </form>

    <div class="footer">We store minimal data. Do not post sensitive info (e.g., national ID). Contact admin if you want your data removed.</div>
  </div>

  <div class="info">
    <div class="card">
      <div class="avatar">DB</div>
      <div class="meta">
        <h4>Why register?</h4>
        <p>Registering helps hospitals and NGOs find nearby donors quickly. Your availability will be visible to searchers.</p>
      </div>
    </div>

    <div class="card">
      <div style="display:flex;gap:12px;align-items:center">
        <div style="width:8px;height:8px;background:var(--accent);border-radius:50%"></div>
        <div>
          <h4 style="margin:0;font-size:14px">Be ready to donate</h4>
          <p style="margin:6px 0 0 0;color:var(--muted);font-size:13px">Update your last donation date when you donate so your availability is accurate.</p>
        </div>
      </div>
    </div>

    <div class="card" style="text-align:center">
      <div style="font-weight:700;color:var(--primary);font-size:14px">Fast tips</div>
      <p style="color:var(--muted);font-size:13px;margin-top:8px">Use a reachable phone number and correct city to appear in local searches. Keep last donation date accurate.</p>
    </div>
  </div>
</div>

</body>
</html>
