<?php
define('HOST','localhost');
define('USER','root');
define('PASS','');
define('DB','kg_db');


$con = mysqli_connect(HOST,USER,PASS,DB);

 
$sql="SELECT * FROM tb_products
WHERE tb_products.available = 0";

$result = mysqli_query($con,$sql);

while(($row = mysqli_fetch_assoc($result)) == true){
	$data[]=$row;
}
echo json_encode($data);
?>