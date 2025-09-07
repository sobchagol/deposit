<?php
require_once __DIR__."/config/db.php";
require_once __DIR__."/includes/auth.php";
require_login();
include __DIR__."/includes/header.php";
include __DIR__."/helpers.php";

$msg = "";
if ($_SERVER["REQUEST_METHOD"]==="POST"){
  $amount = (float)($_POST['amount']??0);
  $uid = $_SESSION['user_id'];
  if ($amount<=0) $msg="Invalid amount";
  else {
    $bal = $conn->query("SELECT balance FROM users WHERE id={$uid}")->fetch_assoc()['balance'];
    if ($bal < $amount) $msg="Insufficient balance.";
    else {
      $stmt = $conn->prepare("INSERT INTO withdraws (user_id, amount) VALUES (?,?)");
      $stmt->bind_param("id", $uid, $amount);
      $msg = $stmt->execute() ? "Withdraw request submitted." : "Failed to submit.";
    }
  }
}
$uid = $_SESSION['user_id'];
$rows = $conn->query("SELECT * FROM withdraws WHERE user_id={$uid} ORDER BY id DESC LIMIT 50");
?>
<div class="card">
  <h2>Withdraw</h2>
  <?php if($msg): ?><div class="alert success"><?php echo esc($msg); ?></div><?php endif; ?>
  <form method="post" class="row">
    <div class="w-50">
      <input class="form-control" type="number" step="0.01" name="amount" placeholder="Amount (৳)" required>
    </div>
    <div class="w-50">
      <button class="btn" type="submit">Request</button>
    </div>
  </form>
</div>
<div class="card">
  <h3>Recent Withdraw Requests</h3>
  <table class="table">
    <tr><th>#</th><th>Amount</th><th>Status</th><th>Time</th></tr>
    <?php while($w = $rows->fetch_assoc()): ?>
      <tr>
        <td><?php echo $w['id']; ?></td>
        <td><?php echo money($w['amount']); ?></td>
        <td><?php echo esc($w['status']); ?></td>
        <td><?php echo esc($w['created_at']); ?></td>
      </tr>
    <?php endwhile; ?>
  </table>
</div>
<?php include __DIR__."/includes/footer.php"; ?>
