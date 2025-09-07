<?php
require_once __DIR__."/config/db.php";
require_once __DIR__."/includes/auth.php";
require_login();
include __DIR__."/includes/header.php";
include __DIR__."/helpers.php";

$msg = "";
if ($_SERVER["REQUEST_METHOD"]==="POST"){
  $amount = (float)($_POST['amount']??0);
  if ($amount<=0) $msg="Invalid amount";
  else {
    $stmt = $conn->prepare("INSERT INTO deposits (user_id, amount, method) VALUES (?,?,?)");
    $uid = $_SESSION['user_id']; $method='manual';
    $stmt->bind_param("ids", $uid, $amount, $method);
    $msg = $stmt->execute() ? "Deposit request submitted. Wait for admin approval." : "Failed to submit.";
  }
}
$uid = $_SESSION['user_id'];
$deps = $conn->query("SELECT * FROM deposits WHERE user_id={$uid} ORDER BY id DESC LIMIT 50");
?>
<div class="card">
  <h2>Deposit</h2>
  <?php if($msg): ?><div class="alert success"><?php echo esc($msg); ?></div><?php endif; ?>
  <form method="post" class="row">
    <div class="w-50">
      <input class="form-control" type="number" step="0.01" name="amount" placeholder="Amount (৳)" required>
    </div>
    <div class="w-50">
      <button class="btn" type="submit">Submit</button>
    </div>
  </form>
</div>
<div class="card">
  <h3>Recent Deposit Requests</h3>
  <table class="table">
    <tr><th>#</th><th>Amount</th><th>Status</th><th>Time</th></tr>
    <?php while($d = $deps->fetch_assoc()): ?>
      <tr>
        <td><?php echo $d['id']; ?></td>
        <td><?php echo money($d['amount']); ?></td>
        <td><?php echo esc($d['status']); ?></td>
        <td><?php echo esc($d['created_at']); ?></td>
      </tr>
    <?php endwhile; ?>
  </table>
</div>
<?php include __DIR__."/includes/footer.php"; ?>
