<?php
session_start();
require_once 'db.php'; // $pdo connection

$error = '';

if (!empty($_SESSION['donor_id'])) {
    header('Location: donor_portal.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['donor_id'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($identifier === '' || $password === '') {
        $error = 'Please enter Donor ID and password.';
    } else {
        // Try find matching donor by donor_id (string)
        $stmt = $pdo->prepare("SELECT id, name, password_hash FROM donors WHERE donor_id = ?");
        $stmt->execute([$identifier]);
        $d = $stmt->fetch(PDO::FETCH_ASSOC);

        // If not found and identifier is numeric → match by numeric id column
        if (!$d && ctype_digit($identifier)) {
            $stmt2 = $pdo->prepare("SELECT id, name, password_hash FROM donors WHERE id = ?");
            $stmt2->execute([(int)$identifier]);
            $d = $stmt2->fetch(PDO::FETCH_ASSOC);
        }

        // Verify hashed password
        if ($d && password_verify($password, $d['password_hash'])) {
            $_SESSION['donor_id'] = $d['id'];
            $_SESSION['donor_name'] = $d['name'];
            header('Location: donor_portal.php');
            exit;
        } else {
            $error = 'Invalid Donor ID or password.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Donor Login</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#f6f7fb; font-family:Inter,system-ui; padding:30px; }
    .card { border-radius:12px; box-shadow:0 6px 28px rgba(0,0,0,0.06); }
    .small-note { font-size:0.9rem; color:#6b7280; }
  </style>
</head>
<body>
<div class="container" style="max-width:520px">
  <div class="card p-4">
    <h4 class="mb-1">Donor Portal Login</h4>
    <p class="small-note mb-3">Login with your Donor ID (alphanumeric) or numeric DB ID and your password.</p>

    <?php if($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off" novalidate>
      <div class="mb-3">
        <label class="form-label">Donor ID</label>
        <input
          class="form-control"
          name="donor_id"
          type="text"
          maxlength="50"
          placeholder="e.g. D000123 or Hira123 (or numeric id)"
          value="<?= htmlspecialchars($_POST['donor_id'] ?? '') ?>"
          required>
      </div>

      <div class="mb-3 position-relative">
        <label class="form-label">Password</label>
        <div class="input-group">
          <input
            id="passwordField"
            class="form-control"
            name="password"
            type="password"
            placeholder="Enter your password"
            required>
          <button type="button" class="btn btn-outline-secondary" id="togglePwd">Show</button>
        </div>
      </div>

      <div class="d-flex gap-2">
        <button class="btn btn-primary w-50" type="submit">Sign in</button>
        <a class="btn btn-outline-secondary w-50" href="index.php">Back</a>
      </div>
    </form>

    <div class="mt-3 small-note">
      <strong>Note:</strong> If you registered with an alphanumeric Donor ID, use that.  
      If you only remember the numeric DB ID, you can enter that too.
    </div>
  </div>
</div>

<script>
document.getElementById('togglePwd').addEventListener('click', function() {
    const f = document.getElementById('passwordField');
    if (f.type === 'password') {
        f.type = 'text';
        this.textContent = 'Hide';
    } else {
        f.type = 'password';
        this.textContent = 'Show';
    }
});
</script>
</body>
</html>
