<?php
require('config_app.php');

$id = $_POST['id'];
$date = $_POST['date'];
$time = $_POST['time'];
$name = $_POST['name'];
$status = $_POST['status'];

$sql1="INSERT INTO tb_transactions (id, date,time, name, status) VALUES
('" . $id . "','" . $date . "','" . $time . "','" . $name . "','" . $status . "')";

$run = mysqli_query($db, $sql1);

?>
