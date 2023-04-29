<?php 
require('config_app.php');

$sql = "SELECT * FROM `tb_product_category` ORDER BY tb_product_category.category ASC";

$con = mysqli_query($con,$sql);

$result = array();

while($row = mysqli_fetch_array($con)){
    array_push($result,array(
        'category'=>$row['category']
    ));
}

echo json_encode(array('result'=>$result));
?>