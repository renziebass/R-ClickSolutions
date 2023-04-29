<?php
require('config.php');
 
$sql="SELECT *
FROM (SELECT
		tb_transactions.id,
		tb_transactions.name,
		tb_transactions.date
		FROM tb_transactions WHERE tb_transactions.status='unpaid') AS A
JOIN (SELECT
		tb_cart.transaction_id,
		SUM(tb_cart.quantity) as items,
		SUM(tb_cart.price*tb_cart.quantity) AS total
        FROM tb_cart
		GROUP BY tb_cart.transaction_id) AS B
ON A.id=B.transaction_id
GROUP BY A.id";

$result = mysqli_query($con,$sql);

while(($row = mysqli_fetch_assoc($result)) == true){
	$data[]=$row;
}
echo json_encode($data);
?>