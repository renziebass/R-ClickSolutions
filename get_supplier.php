<?php 
require('config.php');

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