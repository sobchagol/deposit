<?php
require_once __DIR__."/../config/db.php";
require_once __DIR__."/../includes/auth.php";
require_admin();

$users = $conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'];
$deps  = $conn->query("SELECT IFNULL(SUM(amount),0) s FROM deposits WHERE status='approved'")->fetch_assoc()['s'];
$withs = $conn->query("SELECT IFNULL(SUM(amount),0) s FROM withdraws WHERE status='approved'")->fetch_assoc()['s'];
$offers= $conn->query("SELECT COUNT(*) c FROM offers WHERE is_active=1")->fetch_assoc()['c'];

include __DIR__."/../includes/header.php";
include __DIR__."/../helpers.php";
?>
<div class="grid">
  <div class="card"><h3>Total Users</h3><p><strong><?php echo (int)$users; ?></strong></p></div>
  <div class="card"><h3>Approved Deposits</h3><p><strong>৳ <?php echo money($deps); ?></strong></p></div>
  <div class="card"><h3>Approved Withdraws</h3><p><strong>৳ <?php echo money($withs); ?></strong></p></div>
  <div class="card"><h3>Active Offers</h3><p><strong><?php echo (int)$offers; ?></strong></p></div>
</div>

<div class="card">
  <h3>Actions</h3>
  <p>
    <a class="btn" href="/admin/users.php">Users</a>
    <a class="btn" href="/admin/offers.php">Offers</a>
    <a class="btn" href="/admin/deposits.php">Deposits</a>
    <a class="btn" href="/admin/withdraws.php">Withdraws</a>
    <a class="btn secondary" href="/admin/run_bonus.php">Run Daily Bonus (All)</a>
  </p>
</div>
<?php include __DIR__."/../includes/footer.php"; ?>
