<?php
  define('HOST','https://auth-db445.hstgr.io/');
  define('USER','u186319490_admin123');
  define('PASS','Kg9182022');
  define('DB','u186319490_kg_db');

 
$con = mysqli_connect(HOST,USER,PASS,DB);

$DateNow = $_GET['datenow'];
 
$sql = "SELECT
SUM(tb_payments.total) AS income_today,
COUNT(tb_payments.id) AS customers,
COUNT(tb_cart.product_id) AS items
FROM tb_transactions
JOIN tb_cart ON tb_transactions.id=tb_cart.transaction_id
JOIN tb_payments ON tb_transactions.id=tb_payments.id
WHERE tb_transactions.date='$DateNow' AND tb_transactions.status='paid'";

 
$res = mysqli_query($con,$sql);
 
$result = array();
 
while($row = mysqli_fetch_array($res)){
array_push($result,
array('income_today'=>$row[0]),
array('customers'=>$row[1]),
array('items'=>$row[2]));
}
 
echo json_encode(array("result"=>$result));
 
mysqli_close($con);
 
?>