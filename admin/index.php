<?php
// Admin shares the same login as users, but role must be admin.
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role']==='admin'){
  header("Location: /admin/dashboard.php"); exit;
}
header("Location: /index.php");
exit;
