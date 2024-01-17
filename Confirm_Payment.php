<?php
require('config_app.php');

$id = $_POST['id'];
$date = $_POST['date'];
$time = $_POST['time'];
$total = $_POST['total'];
$payment = $_POST['payment'];
$change =  $_POST['change'];

$sql1="INSERT INTO tb_payments (id, date,time, total, payment, change1) 
VALUES ('" . $id . "','" . $date . "','" . $time . "','" . $total . "','" . $payment . "','" . $change . "')";

$run = mysqli_query($db, $sql1);

?>
