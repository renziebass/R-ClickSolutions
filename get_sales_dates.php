<?php 
require('config_app.php');


$sql = "SELECT DATE_FORMAT(tb_payments.date,'%M %d,%Y') AS date1,
tb_payments.date,
SUM(tb_cart.price*tb_cart.quantity) AS sales
FROM tb_payments 
JOIN tb_cart ON tb_payments.id=tb_cart.transaction_id
GROUP BY tb_payments.date DESC";

$con = mysqli_query($con,$sql);

$result = array();

while($row = mysqli_fetch_array($con)){
    array_push($result,array(
        'date1'=>$row['date1']
    ));
}

echo json_encode(array('result'=>$result));
?>