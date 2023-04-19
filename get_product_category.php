<?php 

define('HOST','localhost');
define('USER','u186319490_admin123');
define('PASS','Kg9182022');
define('DB','u186319490_kg_db');

$con = mysqli_connect(HOST,USER,PASS,DB);

$sql = "SELECT * FROM tb_product_category";

$con = mysqli_query($con,$sql);

$result = array();

while($row = mysqli_fetch_array($con)){
    array_push($result,array(
        'category'=>$row['category']
    ));
}

echo json_encode(array('result'=>$result));
?>