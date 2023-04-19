<?php
  define('HOST','https://auth-db445.hstgr.io/');
  define('USER','u186319490_admin123');
  define('PASS','Kg9182022');
  define('DB','u186319490_kg_db');


$con = mysqli_connect(HOST,USER,PASS,DB);

 
$sql="SELECT * FROM tb_products
WHERE tb_products.available <= 5 AND tb_products.available
NOT IN (SELECT tb_products.available FROM tb_products WHERE tb_products.available='0');";

$result = mysqli_query($con,$sql);

while(($row = mysqli_fetch_assoc($result)) == true){
	$data[]=$row;
}
echo json_encode($data);
?>