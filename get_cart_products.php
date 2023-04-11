<?php
define('HOST','localhost');
define('USER','root');
define('PASS','');
define('DB','kg_db');


$con = mysqli_connect(HOST,USER,PASS,DB);

$transaction_id = $_GET['transaction_id'];
 
$sql="SELECT *
FROM (SELECT
		tb_products.id,
		tb_products.product_brand,
		CONCAT(tb_products.mc_brand,'-',tb_products.mc_model,'-',tb_products.category) AS specification,
		  tb_products.price
		FROM tb_cart LEFT JOIN tb_products ON tb_cart.product_id=tb_products.id) AS A
JOIN (SELECT
		tb_cart.product_id,
		SUM(tb_cart.quantity) AS quantity,
		SUM(tb_cart.price*tb_cart.quantity) AS total
		FROM tb_cart WHERE tb_cart.transaction_id='$transaction_id'
		GROUP BY tb_cart.product_id) AS B
ON A.id=B.product_id
GROUP BY B.product_id";

$result = mysqli_query($con,$sql);

while(($row = mysqli_fetch_assoc($result)) == true){
	$data[]=$row;
}
echo json_encode($data);
?>