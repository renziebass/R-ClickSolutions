<?php
  define('HOST','https://auth-db445.hstgr.io/');
  define('USER','u186319490_admin123');
  define('PASS','Kg9182022');
  define('DB','u186319490_kg_db');

 
$con = mysqli_connect(HOST,USER,PASS,DB);

$id = $_GET['id'];
 
$sql="SELECT tb_products.available FROM tb_products WHERE tb_products.id='$id'";

 
$res = mysqli_query($con,$sql);
 
$result = array();
 
while($row = mysqli_fetch_array($res)){
array_push($result,
array('available'=>$row[0]));
}
 
echo json_encode(array("result"=>$result));
 
mysqli_close($con);
 
?>