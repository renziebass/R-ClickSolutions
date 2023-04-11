<?php
define('HOST','localhost');
define('USER','root');
define('PASS','');
define('DB','kg_db');


$con = mysqli_connect(HOST,USER,PASS,DB);

 
$sql="SELECT
tb_transactions.name,
CONCAT(tb_transactions.date,' ',tb_transactions.time) AS date_time,
SUM(tb_cart.total) AS amount
FROM tb_transactions
INNER JOIN tb_cart ON tb_transactions.id=tb_cart.transaction_id
WHERE tb_transactions.status='unpaid'";

$result = mysqli_query($con,$sql);

while(($row = mysqli_fetch_assoc($result)) == true){
	$data[]=$row;
}
echo json_encode($data);
?>