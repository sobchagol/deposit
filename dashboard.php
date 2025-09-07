<?php
require_once __DIR__."/config/db.php";
require_once __DIR__."/includes/auth.php";
require_login();

// Auto-credit daily bonus for this user (once per day)
$user_id = $_SESSION['user_id'];

$today = date('Y-m-d');
$stmt = $conn->prepare("SELECT id, daily_bonus, last_credited FROM purchases WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$credited = 0.0;
while($p = $res->fetch_assoc()){
  if ($p['last_credited'] !== $today) {
    $conn->begin_transaction();
    try {
      $upd = $conn->prepare("UPDATE purchases SET last_credited=? WHERE id=?");
      $upd->bind_param("si", $today, $p['id']);
      $upd->execute();

      $upd2 = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id=?");
      $upd2->bind_param("di", $p['daily_bonus'], $user_id);
      $upd2->execute();

      $insTx = $conn->prepare("INSERT INTO transactions (user_id, type, amount, meta) VALUES (?,?,?,?)");
      $typ = 'bonus'; $meta = 'Daily bonus';
      $insTx->bind_param("isds", $user_id, $typ, $p['daily_bonus'], $meta);
      $insTx->execute();

      $conn->commit();
      $credited += (float)$p['daily_bonus'];
    } catch (Throwable $e) {
      $conn->rollback();
    }
  }
}
$stmt = $conn->prepare("SELECT name, balance FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

include __DIR__."/includes/header.php";
include __DIR__."/helpers.php";
?>
<div class="grid">
  <div class="card">
    <h3>Welcome, <?php echo esc($user['name']); ?> 👋</h3>
    <p>Wallet Balance: <strong>৳ <?php echo money($user['balance']); ?></strong></p>
    <?php if($credited>0): ?>
      <div class="alert success">Daily bonus credited: ৳ <?php echo money($credited); ?></div>
    <?php endif; ?>
    <p><a class="btn" href="/offers.php">Browse Offers</a></p>
  </div>
  <div class="card">
    <h3>Quick Actions</h3>
    <p><a class="btn" href="/deposit.php">Deposit</a> <a class="btn" href="/withdraw.php">Withdraw</a></p>
    <p><a class="btn secondary" href="/wallet.php">Transactions</a></p>
  </div>
</div>
<?php include __DIR__."/includes/footer.php"; ?>
