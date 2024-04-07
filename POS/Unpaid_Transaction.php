<?php
require('config_app.php');

$id = $_POST['id'];
$date = $_POST['date'];
$time = $_POST['time'];
$name = $_POST['name'];
$status = $_POST['status'];

$sql1="UPDATE tb_transactions
SET tb_transactions.date='$date',
tb_transactions.time='$time',
tb_transactions.status='paid'
WHERE tb_transactions.id='$id'";

$run = mysqli_query($db, $sql1);

?>
