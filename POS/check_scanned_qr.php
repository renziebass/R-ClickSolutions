<?php
require('config_app.php');

$product_id = $_GET['product_id'];
 
$sql="SELECT tb_products.available, tb_products.price,
CONCAT(tb_products.mc_brand,'-',tb_products.mc_model,'-',tb_products.category) AS specification,
tb_products.product_brand
FROM tb_products WHERE tb_products.id='$product_id'";

$result = mysqli_query($con,$sql);

while(($row = mysqli_fetch_assoc($result)) == true){
	$data[]=$row;
}
echo json_encode($data);
?>