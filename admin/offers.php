<?php
require_once __DIR__."/../config/db.php";
require_once __DIR__."/../includes/auth.php";
require_admin();
include __DIR__."/../includes/header.php";
include __DIR__."/../helpers.php";

$msg="";
if ($_SERVER["REQUEST_METHOD"]==="POST"){
  if (isset($_POST['create'])){
    $title = $_POST['title']??'';
    $description = $_POST['description']??'';
    $price = (float)($_POST['price']??0);
    $bonus = (float)($_POST['daily_bonus']??0);
    $stmt = $conn->prepare("INSERT INTO offers (title,description,price,daily_bonus,is_active) VALUES (?,?,?,?,1)");
    $stmt->bind_param("ssdd",$title,$description,$price,$bonus);
    $msg = $stmt->execute() ? "Offer created." : "Create failed.";
  }
  if (isset($_POST['toggle'])){
    $id = (int)$_POST['toggle'];
    $conn->query("UPDATE offers SET is_active = IF(is_active=1,0,1) WHERE id={$id}");
    $msg="Offer toggled.";
  }
  if (isset($_POST['delete'])){
    $id = (int)$_POST['delete'];
    $conn->query("DELETE FROM offers WHERE id={$id}");
    $msg="Offer deleted.";
  }
}

$res = $conn->query("SELECT * FROM offers ORDER BY id DESC");
?>
<div class="card">
  <h2>Manage Offers</h2>
  <?php if($msg): ?><div class="alert success"><?php echo esc($msg); ?></div><?php endif; ?>
  <form method="post" class="row">
    <input class="form-control w-33" name="title" placeholder="Title" required>
    <input class="form-control w-33" name="price" type="number" step="0.01" placeholder="Price" required>
    <input class="form-control w-33" name="daily_bonus" type="number" step="0.01" placeholder="Daily Bonus" required>
    <textarea class="form-control w-100" name="description" placeholder="Description"></textarea>
    <button class="btn" name="create">Create Offer</button>
  </form>
  <table class="table">
    <tr><th>ID</th><th>Title</th><th>Price</th><th>Daily Bonus</th><th>Active</th><th>Actions</th></tr>
    <?php while($o = $res->fetch_assoc()): ?>
      <tr>
        <td><?php echo $o['id']; ?></td>
        <td><?php echo esc($o['title']); ?></td>
        <td><?php echo money($o['price']); ?></td>
        <td><?php echo money($o['daily_bonus']); ?></td>
        <td><?php echo $o['is_active']?'Yes':'No'; ?></td>
        <td>
          <form method="post" style="display:inline-block">
            <button class="btn secondary" name="toggle" value="<?php echo $o['id']; ?>">Activate/Deactivate</button>
          </form>
          <form method="post" style="display:inline-block" onsubmit="return confirm('Delete offer?')">
            <button class="btn" name="delete" value="<?php echo $o['id']; ?>">Delete</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
</div>
<?php include __DIR__."/../includes/footer.php"; ?>
