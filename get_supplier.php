<?php 

define('HOST','localhost');
define('USER','root');
define('PASS','');
define('DB','kg_db');

$con = mysqli_connect(HOST,USER,PASS,DB);

$sql = "SELECT * FROM tb_supplier";

$con = mysqli_query($con,$sql);

$result = array();

while($row = mysqli_fetch_array($con)){
    array_push($result,array(
        'id'=>$row['id']
    ));
}

echo json_encode(array('result'=>$result));
?>