<?php
require('config_app.php');

$transaction_id = $_GET['transaction_id'];
 
$sql = "SELECT
SUM(tb_cart.price*tb_cart.quantity) AS total,
tb_payments.payment,
tb_payments.change1
FROM tb_payments
LEFT JOIN tb_cart ON tb_payments.id=tb_cart.transaction_id
WHERE tb_payments.id='$transaction_id'";

 
$res = mysqli_query($con,$sql);
 
$result = array();
 
while($row = mysqli_fetch_array($res)){
array_push($result,
array('total'=>$row[0]),
array('payment'=>$row[1]),
array('change1'=>$row[2]));
}
 
echo json_encode(array("result"=>$result));
 
mysqli_close($con);
 
?>