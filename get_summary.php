<?php
 define('HOST','localhost');
 define('USER','u186319490_admin123');
 define('PASS','Kg9182022');
 define('DB','u186319490_kg_db');

 
$con = mysqli_connect(HOST,USER,PASS,DB);

$DateNow = $_GET['datenow'];
 
$sql = "SELECT
CONCAT(FORMAT(SUM(tb_cart.price), 2)) AS income_today,
(SELECT COUNT(tb_payments.id)
FROM tb_payments WHERE tb_payments.date='$DateNow') AS customers,
(SELECT COUNT(tb_cart.product_id)) AS items
FROM tb_transactions
LEFT JOIN tb_payments ON tb_transactions.id=tb_payments.id
RIGHT JOIN tb_cart ON tb_transactions.id=tb_cart.transaction_id
WHERE tb_transactions.status='paid' AND tb_transactions.date='$DateNow'";

 
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