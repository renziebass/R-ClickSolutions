<?php
require('config_app.php');

$sql="SELECT
CONCAT(tb_products.mc_brand,' ',tb_products.mc_model,' ',tb_products.category) AS specification,
tb_products.product_brand,
tb_products.available
FROM tb_products
WHERE tb_products.available <= 5 AND tb_products.available
NOT IN (SELECT tb_products.available FROM tb_products WHERE tb_products.available='0')";

$result = mysqli_query($con,$sql);

while(($row = mysqli_fetch_assoc($result)) == true){
	$data[]=$row;
}
echo json_encode($data);

?>