<?php
require_once __DIR__."/../config/db.php";
require_once __DIR__."/../includes/auth.php";
require_admin();

$today = date('Y-m-d');
$total=0.0;
$res = $conn->query("SELECT id, user_id, daily_bonus, last_credited FROM purchases");
while($p = $res->fetch_assoc()){
  if ($p['last_credited'] !== $today){
    $conn->begin_transaction();
    try{
      $conn->query("UPDATE purchases SET last_credited='{$today}' WHERE id={$p['id']}");
      $conn->query("UPDATE users SET balance = balance + {$p['daily_bonus']} WHERE id={$p['user_id']}");
      $type='bonus'; $meta='Daily bonus (admin run)';
      $stmt = $conn->prepare("INSERT INTO transactions (user_id,type,amount,meta) VALUES (?,?,?,?)");
      $stmt->bind_param("isds",$p['user_id'],$type,$p['daily_bonus'],$meta);
      $stmt->execute();
      $conn->commit();
      $total += (float)$p['daily_bonus'];
    }catch(Throwable $e){ $conn->rollback(); }
  }
}
echo "Run done. Total credited today: ৳ ".number_format($total,2);
