<?php
require('config_app.php');

$transaction_id = $_GET['transaction_id'];
 
$sql = "UPDATE tb_products
LEFT JOIN tb_cart ON tb_products.id=tb_cart.product_id
SET tb_products.available=tb_products.available+(SELECT SUM(tb_cart.quantity) FROM tb_cart WHERE tb_cart.transaction_id='$transaction_id')
WHERE tb_cart.transaction_id='$transaction_id'";
 
$res = mysqli_query($db,$sql);

?>