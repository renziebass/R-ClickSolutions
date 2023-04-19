<?php
 define('HOST','localhost');
 define('USER','u186319490_admin123');
 define('PASS','Kg9182022');
 define('DB','u186319490_kg_db');


$con = mysqli_connect(HOST,USER,PASS,DB);

$category = $_GET['category'];
$brand = $_GET['brand'];
$model = $_GET['model'];
 
$sql="SELECT tb_products.id, tb_products.product_brand,
CONCAT(tb_products.available,'-',tb_products.stocks) AS stocks,
tb_products.price
FROM tb_products
WHERE
tb_products.category = '$category'
AND tb_products.mc_brand = '$brand'
AND tb_products.mc_model = '$model'";

$result = mysqli_query($con,$sql);

while(($row = mysqli_fetch_assoc($result)) == true){
	$data[]=$row;
}
echo json_encode($data);
?>