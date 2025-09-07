<?php
require_once __DIR__."/../config/db.php";
require_once __DIR__."/../includes/auth.php";
require_admin();
include __DIR__."/../includes/header.php";
include __DIR__."/../helpers.php";

$msg = "";
if ($_SERVER["REQUEST_METHOD"]==="POST"){
  if (isset($_POST['adjust_id'])){
    $uid = (int)$_POST['adjust_id'];
    $amount = (float)$_POST['amount'];
    $conn->begin_transaction();
    try{
      $conn->query("UPDATE users SET balance = balance + {$amount} WHERE id={$uid}");
      $type='adjust'; $meta='Admin adjust';
      $stmt = $conn->prepare("INSERT INTO transactions (user_id,type,amount,meta) VALUES (?,?,?,?)");
      $stmt->bind_param("isds", $uid, $type, $amount, $meta);
      $stmt->execute();
      $conn->commit();
      $msg="Balance adjusted.";
    }catch(Throwable $e){
      $conn->rollback(); $msg="Failed.";
    }
  }
  if (isset($_POST['toggle_status'])){
    $uid = (int)$_POST['toggle_status'];
    $conn->query("UPDATE users SET status = IF(status='active','banned','active') WHERE id={$uid}");
    $msg="Status toggled.";
  }
}

$res = $conn->query("SELECT id,name,email,phone,balance,role,status FROM users ORDER BY id DESC LIMIT 200");
?>
<div class="card">
  <h2>Users</h2>
  <?php if($msg): ?><div class="alert success"><?php echo esc($msg); ?></div><?php endif; ?>
  <table class="table">
    <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Balance</th><th>Role</th><th>Status</th><th>Actions</th></tr>
    <?php while($u = $res->fetch_assoc()): ?>
      <tr>
        <td><?php echo $u['id']; ?></td>
        <td><?php echo esc($u['name']); ?></td>
        <td><?php echo esc($u['email']); ?></td>
        <td><?php echo esc($u['phone']); ?></td>
        <td><?php echo money($u['balance']); ?></td>
        <td><?php echo esc($u['role']); ?></td>
        <td><?php echo esc($u['status']); ?></td>
        <td>
          <form method="post" style="display:inline-block">
            <input type="hidden" name="toggle_status" value="<?php echo $u['id']; ?>">
            <button class="btn secondary" onclick="return confirm('Toggle status?')">Ban/Unban</button>
          </form>
          <form method="post" style="display:inline-block">
            <input type="hidden" name="adjust_id" value="<?php echo $u['id']; ?>">
            <input class="form-control" style="width:120px;display:inline-block" name="amount" type="number" step="0.01" placeholder="+/- amount">
            <button class="btn">Adjust</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
</div>
<?php include __DIR__."/../includes/footer.php"; ?>
