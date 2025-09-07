<?php
require_once __DIR__."/config/db.php";
require_once __DIR__."/includes/auth.php";
require_login();
include __DIR__."/includes/header.php";
include __DIR__."/helpers.php";

$user_id = $_SESSION['user_id'];
$res = $conn->query("SELECT * FROM transactions WHERE user_id={$user_id} ORDER BY id DESC LIMIT 200");
$u = $conn->query("SELECT balance FROM users WHERE id={$user_id}")->fetch_assoc();
?>
<div class="card">
  <h2>Wallet & Transactions</h2>
  <p>Current Balance: <strong>৳ <?php echo money($u['balance']); ?></strong></p>
  <table class="table">
    <tr><th>#</th><th>Type</th><th>Amount</th><th>Meta</th><th>Time</th></tr>
    <?php while($t = $res->fetch_assoc()): ?>
      <tr>
        <td><?php echo $t['id']; ?></td>
        <td><?php echo esc($t['type']); ?></td>
        <td><?php echo money($t['amount']); ?></td>
        <td><?php echo esc($t['meta']); ?></td>
        <td><?php echo esc($t['created_at']); ?></td>
      </tr>
    <?php endwhile; ?>
  </table>
</div>
<?php include __DIR__."/includes/footer.php"; ?>
