<?php
define('HOST','localhost');
define('USER','root');
define('PASS','');
define('DB','kg_db');

 
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