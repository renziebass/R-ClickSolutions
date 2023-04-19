<?php
 define('HOST','localhost');
 define('USER','u186319490_admin123');
 define('PASS','Kg9182022');
 define('DB','u186319490_kg_db');


$con = mysqli_connect(HOST,USER,PASS,DB);
 
$sql="SELECT *
FROM (SELECT
		tb_transactions.id,
		tb_transactions.name,
		tb_transactions.date
		FROM tb_transactions WHERE tb_transactions.status='unpaid') AS A
JOIN (SELECT
		tb_cart.transaction_id,
		SUM(tb_cart.quantity) as items,
      	SUM(tb_cart.total) AS total
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