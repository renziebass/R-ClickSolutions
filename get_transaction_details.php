<?php
define('HOST','localhost');
define('USER','root');
define('PASS','');
define('DB','kg_db');

 
$con = mysqli_connect(HOST,USER,PASS,DB);

$transaction_id = $_GET['transaction_id'];
 
$sql = "SELECT
SUM(tb_cart.quantity) AS items,
SUM(tb_cart.price*tb_cart.quantity) AS total,
tb_transactions.name
FROM tb_cart
JOIN tb_transactions ON tb_cart.transaction_id=tb_transactions.id
WHERE tb_cart.transaction_id='$transaction_id'
GROUP BY tb_cart.transaction_id";

 
$res = mysqli_query($con,$sql);
 
$result = array();
 
while($row = mysqli_fetch_array($res)){
array_push($result,
array('items'=>$row[0]),
array('total'=>$row[1]),
array('name'=>$row[2]));
}
 
echo json_encode(array("result"=>$result));
 
mysqli_close($con);
 
?>