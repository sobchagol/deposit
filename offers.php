<?php
require_once __DIR__."/config/db.php";
require_once __DIR__."/includes/auth.php";
require_login();
include __DIR__."/includes/header.php";
include __DIR__."/helpers.php";

$msg = "";
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['offer_id'])){
  $offer_id = (int)$_POST['offer_id'];
  $user_id = $_SESSION['user_id'];

  $stmt = $conn->prepare("SELECT id, price, daily_bonus FROM offers WHERE id=? AND is_active=1");
  $stmt->bind_param("i", $offer_id);
  $stmt->execute();
  if ($offer = $stmt->get_result()->fetch_assoc()){
    $conn->begin_transaction();
    try{
      $bq = $conn->prepare("SELECT balance FROM users WHERE id=? FOR UPDATE");
      $bq->bind_param("i",$user_id);
      $bq->execute();
      $bal = (float)$bq->get_result()->fetch_assoc()['balance'];
      if ($bal < (float)$offer['price']) { throw new Exception("Insufficient balance"); }

      $upd = $conn->prepare("UPDATE users SET balance=balance-? WHERE id=?");
      $upd->bind_param("di", $offer['price'], $user_id);
      $upd->execute();

      $ins = $conn->prepare("INSERT INTO purchases (user_id, offer_id, price, daily_bonus) VALUES (?,?,?,?)");
      $ins->bind_param("iidd", $user_id, $offer_id, $offer['price'], $offer['daily_bonus']);
      $ins->execute();

      $tx = $conn->prepare("INSERT INTO transactions (user_id,type,amount,meta) VALUES (?,?,?,?)");
      $t='purchase'; $meta='Offer #'.$offer_id; $neg = -1 * (float)$offer['price'];
      $tx->bind_param("isds", $user_id, $t, $neg, $meta);
      $tx->execute();

      $conn->commit();
      $msg = "Offer purchased successfully.";
    }catch(Throwable $e){
      $conn->rollback();
      $msg = "Purchase failed: ".$e->getMessage();
    }
  } else { $msg = "Offer not available."; }
}

$res = $conn->query("SELECT * FROM offers WHERE is_active=1 ORDER BY id DESC");
?>
<div class="card"><h2>Offers</h2>
<?php if($msg): ?><div class="alert <?php echo strpos($msg,'failed')!==false?'error':'success';?>"><?php echo esc($msg); ?></div><?php endif; ?>
<div class="grid">
<?php while($o = $res->fetch_assoc()): ?>
  <div class="card">
    <h3><?php echo esc($o['title']); ?></h3>
    <p><?php echo nl2br(esc($o['description'])); ?></p>
    <p>Price: <strong>৳ <?php echo money($o['price']); ?></strong></p>
    <p>Daily Bonus: <span class="badge">৳ <?php echo money($o['daily_bonus']); ?></span></p>
    <form method="post">
      <input type="hidden" name="offer_id" value="<?php echo $o['id']; ?>">
      <button class="btn" type="submit">Buy Offer</button>
    </form>
  </div>
<?php endwhile; ?>
</div>
</div>
<?php include __DIR__."/includes/footer.php"; ?>
