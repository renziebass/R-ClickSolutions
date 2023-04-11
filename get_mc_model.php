<?php 

define('HOST','localhost');
define('USER','root');
define('PASS','');
define('DB','kg_db');

$con = mysqli_connect(HOST,USER,PASS,DB);

$brand = $_GET['brand'];

$sql = "SELECT * FROM tb_mc_model WHERE tb_mc_model.brand='$brand'";

$con = mysqli_query($con,$sql);

$result = array();

while($row = mysqli_fetch_array($con)){
    array_push($result,array(
        'model'=>$row['model']  
    ));
}

echo json_encode(array('result'=>$result));
?>