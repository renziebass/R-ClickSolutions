<?php
require('config_app.php');

$transaction_id = $_GET['transaction_id'];
 
$sql = "SELECT
SUM(tb_cart.quantity) AS items,
SUM(tb_cart.price*tb_cart.quantity) AS total
FROM tb_cart
WHERE tb_cart.transaction_id='$transaction_id'
GROUP BY tb_cart.transaction_id;";

 
$res = mysqli_query($con,$sql);
 
$result = array();
 
while($row = mysqli_fetch_array($res)){
array_push($result,
array('items'=>$row[0]),
array('total'=>$row[1]));
}
 
echo json_encode(array("result"=>$result));
 
mysqli_close($con);
 
?>