<?php
require('config_app.php');

$transaction_id = $_GET['transaction_id'];
 
$sql = "DELETE tb_cart FROM tb_cart WHERE tb_cart.transaction_id='$transaction_id'";
 
$res = mysqli_query($db,$sql);

?>
