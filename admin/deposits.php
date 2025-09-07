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
      $row = $conn->query("SELECT user_id, amount FROM deposits WHERE id={$id} FOR UPDATE")->fetch_assoc();
      if ($row){
        $uid = (int)$row['user_id']; $amt = (float)$row['amount'];
        $conn->query("UPDATE deposits SET status='approved' WHERE id={$id}");
        $conn->query("UPDATE users SET balance = balance + {$amt} WHERE id={$uid}");
        $type='deposit'; $meta='Admin approved deposit #'.$id;
        $stmt = $conn->prepare("INSERT INTO transactions (user_id,type,amount,meta) VALUES (?,?,?,?)");
        $stmt->bind_param("isds",$uid,$type,$amt,$meta);
        $stmt->execute();
        $msg="Deposit approved.";
      }
      $conn->commit();
    }catch(Throwable $e){ $conn->rollback(); $msg="Failed."; }
  }
  if (isset($_POST['reject'])){
    $conn->query("UPDATE deposits SET status='rejected' WHERE id={$id}");
    $msg="Deposit rejected.";
  }
}

$res = $conn->query("SELECT d.*, u.name FROM deposits d JOIN users u ON u.id=d.user_id ORDER BY d.id DESC LIMIT 200");
?>
<div class="card">
  <h2>Deposits</h2>
  <?php if($msg): ?><div class="alert success"><?php echo esc($msg); ?></div><?php endif; ?>
  <table class="table">
    <tr><th>#</th><th>User</th><th>Amount</th><th>Method</th><th>Status</th><th>Time</th><th>Action</th></tr>
    <?php while($d=$res->fetch_assoc()): ?>
      <tr>
        <td><?php echo $d['id']; ?></td>
        <td><?php echo esc($d['name']); ?></td>
        <td><?php echo money($d['amount']); ?></td>
        <td><?php echo esc($d['method']); ?></td>
        <td><?php echo esc($d['status']); ?></td>
        <td><?php echo esc($d['created_at']); ?></td>
        <td>
          <form method="post" style="display:inline-block">
            <input type="hidden" name="id" value="<?php echo $d['id']; ?>">
            <button class="btn" name="approve">Approve</button>
          </form>
          <form method="post" style="display:inline-block">
            <input type="hidden" name="id" value="<?php echo $d['id']; ?>">
            <button class="btn secondary" name="reject">Reject</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
</div>
<?php include __DIR__."/../includes/footer.php"; ?>
