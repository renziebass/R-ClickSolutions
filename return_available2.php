<?php
  define('HOST','https://auth-db445.hstgr.io/');
  define('USER','u186319490_admin123');
  define('PASS','Kg9182022');
  define('DB','u186319490_kg_db');

 
$con = mysqli_connect(HOST,USER,PASS,DB);

$transaction_id = $_GET['transaction_id'];
 
$sql = "UPDATE tb_products
LEFT JOIN tb_cart ON tb_products.id=tb_cart.product_id
RIGHT JOIN tb_transactions ON tb_cart.transaction_id=tb_transactions.id
SET tb_products.available=tb_products.available+(SELECT COUNT(tb_cart.product_id) FROM tb_cart WHERE tb_cart.transaction_id='$transaction_id')
WHERE tb_transactions.id='$transaction_id' AND tb_transactions.id=tb_cart.transaction_id";
 
$res = mysqli_query($con,$sql);

 
mysqli_close($con);
 
?>