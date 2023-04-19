<?php 

define('HOST','https://auth-db445.hstgr.io/');
define('USER','u186319490_admin123');
define('PASS','Kg9182022');
define('DB','u186319490_kg_db');

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