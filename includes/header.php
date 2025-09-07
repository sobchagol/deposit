<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Deposit System</title>
  <link rel="stylesheet" href="/styles.css">
</head>
<body>
<header class="topbar">
  <div class="brand"><a href="/dashboard.php">Deposit System</a></div>
  <nav>
    <?php if(isset($_SESSION['user_id'])): ?>
      <a href="/dashboard.php">Dashboard</a>
      <a href="/offers.php">Offers</a>
      <a href="/wallet.php">Wallet</a>
      <a href="/profile.php">Profile</a>
      <?php if(isset($_SESSION['role']) && $_SESSION['role']==='admin'): ?>
        <a href="/admin/dashboard.php">Admin</a>
      <?php endif; ?>
      <a href="/logout.php">Logout</a>
    <?php else: ?>
      <a href="/index.php">Login</a>
      <a href="/register.php">Register</a>
    <?php endif; ?>
  </nav>
</header>
<main class="container">
