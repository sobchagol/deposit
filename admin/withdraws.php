<?php
require_once __DIR__."/../config/db.php";
require_once __DIR__."/../includes/auth.php";
require_admin();
include __DIR__."/../includes/header.php";
include __DIR__."/../helpers.php";

$msg="";
if ($_SERVER["REQUEST_METHOD"]==="POST"){
  $id = (int)($_POST['id']??0);
  if (isset($_POST['approve'])){
    $conn->begin_transaction();
    try{
      $row = $conn->query("SELECT user_id, amount FROM withdraws WHERE id={$id} FOR UPDATE")->fetch_assoc();
      if ($row){
        $uid = (int)$row['user_id']; $amt = (float)$row['amount'];
        $bal = (float)$conn->query("SELECT balance FROM users WHERE id={$uid} FOR UPDATE")->fetch_assoc()['balance'];
        if ($bal >= $amt){
          $conn->query("UPDATE withdraws SET status='approved' WHERE id={$id}");
          $conn->query("UPDATE users SET balance = balance - {$amt} WHERE id={$uid}");
          $type='withdraw'; $meta='Admin approved withdraw #'.$id;
          $stmt = $conn->prepare("INSERT INTO transactions (user_id,type,amount,meta) VALUES (?,?,?,?)");
          $neg = -1 * $amt;
          $stmt->bind_param("isds",$uid,$type,$neg,$meta);
          $stmt->execute();
          $msg="Withdraw approved.";
        } else {
          $msg="Insufficient user balance.";
        }
      }
      $conn->commit();
    }catch(Throwable $e){ $conn->rollback(); $msg="Failed."; }
  }
  if (isset($_POST['reject'])){
    $conn->query("UPDATE withdraws SET status='rejected' WHERE id={$id}");
    $msg="Withdraw rejected.";
  }
}

$res = $conn->query("SELECT w.*, u.name FROM withdraws w JOIN users u ON u.id=w.user_id ORDER BY w.id DESC LIMIT 200");
?>
<div class="card">
  <h2>Withdraws</h2>
  <?php if($msg): ?><div class="alert success"><?php echo esc($msg); ?></div><?php endif; ?>
  <table class="table">
    <tr><th>#</th><th>User</th><th>Amount</th><th>Status</th><th>Time</th><th>Action</th></tr>
    <?php while($w=$res->fetch_assoc()): ?>
      <tr>
        <td><?php echo $w['id']; ?></td>
        <td><?php echo esc($w['name']); ?></td>
        <td><?php echo money($w['amount']); ?></td>
        <td><?php echo esc($w['status']); ?></td>
        <td><?php echo esc($w['created_at']); ?></td>
        <td>
          <form method="post" style="display:inline-block">
            <input type="hidden" name="id" value="<?php echo $w['id']; ?>">
            <button class="btn" name="approve">Approve</button>
          </form>
          <form method="post" style="display:inline-block">
            <input type="hidden" name="id" value="<?php echo $w['id']; ?>">
            <button class="btn secondary" name="reject">Reject</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
</div>
<?php include __DIR__."/../includes/footer.php"; ?>
