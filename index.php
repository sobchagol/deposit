<?php
session_start();
require_once __DIR__."/config/db.php";

$msg = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, password, role, status FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        if ($row['status'] !== 'active') {
            $msg = "Your account is banned.";
        } elseif (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = (int)$row['id'];
            $_SESSION['role'] = $row['role'];
            header("Location: ".($row['role']==='admin' ? "/admin/dashboard.php" : "/dashboard.php"));
            exit;
        } else { $msg = "Invalid credentials."; }
    } else { $msg = "User not found."; }
}
include __DIR__."/includes/header.php";
?>
<div class="card">
  <h2>Login</h2>
  <?php if($msg): ?><div class="alert error"><?php echo $msg; ?></div><?php endif; ?>
  <form method="post">
    <input class="form-control" type="email" name="email" placeholder="Email" required>
    <input class="form-control" type="password" name="password" placeholder="Password" required>
    <button class="btn" type="submit">Login</button>
    <a class="btn secondary" href="/register.php">Register</a>
  </form>
</div>
<?php include __DIR__."/includes/footer.php"; ?>
