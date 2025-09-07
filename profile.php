<?php
require_once __DIR__."/config/db.php";
require_once __DIR__."/includes/auth.php";
require_login();
include __DIR__."/includes/header.php";
include __DIR__."/helpers.php";

$user_id = $_SESSION['user_id'];
$msg = "";
if ($_SERVER["REQUEST_METHOD"]==="POST"){
  $name = trim($_POST['name']??'');
  $phone = trim($_POST['phone']??'');
  $address = trim($_POST['address']??'');
  $stmt = $conn->prepare("UPDATE users SET name=?, phone=?, address=? WHERE id=?");
  $stmt->bind_param("sssi", $name, $phone, $address, $user_id);
  $msg = $stmt->execute() ? "Profile updated." : "Update failed.";
}

$stmt = $conn->prepare("SELECT name,email,phone,address,balance FROM users WHERE id=?");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
?>
<div class="card">
  <h2>Profile</h2>
  <?php if($msg): ?><div class="alert success"><?php echo esc($msg); ?></div><?php endif; ?>
  <form method="post" class="row">
    <div class="w-50">
      <label>Name</label>
      <input class="form-control" name="name" value="<?php echo esc($u['name']); ?>">
    </div>
    <div class="w-50">
      <label>Email (readonly)</label>
      <input class="form-control" value="<?php echo esc($u['email']); ?>" readonly>
    </div>
    <div class="w-50">
      <label>Phone</label>
      <input class="form-control" name="phone" value="<?php echo esc($u['phone']); ?>">
    </div>
    <div class="w-100">
      <label>Address</label>
      <input class="form-control" name="address" value="<?php echo esc($u['address']); ?>">
    </div>
    <div class="w-100">
      <button class="btn">Save</button>
    </div>
  </form>
</div>
<?php include __DIR__."/includes/footer.php"; ?>
