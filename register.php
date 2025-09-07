<?php
require_once __DIR__."/config/db.php";
$msg = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$name || !$email || !$password) { $msg="All fields required."; }
    else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (name,email,password) VALUES (?,?,?)");
        $stmt->bind_param("sss", $name, $email, $hash);
        if ($stmt->execute()) {
            header("Location: /index.php"); exit;
        } else { $msg = "Email already used or DB error."; }
    }
}
include __DIR__."/includes/header.php";
?>
<div class="card">
  <h2>Register</h2>
  <?php if($msg): ?><div class="alert error"><?php echo $msg; ?></div><?php endif; ?>
  <form method="post">
    <input class="form-control" name="name" placeholder="Full name" required>
    <input class="form-control" type="email" name="email" placeholder="Email" required>
    <input class="form-control" type="password" name="password" placeholder="Password" required>
    <button class="btn" type="submit">Create Account</button>
  </form>
</div>
<?php include __DIR__."/includes/footer.php"; ?>
