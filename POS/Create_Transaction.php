<?php
require "DataBase.php";
$db = new DataBase();
if ($db->dbConnect()) {
    if ($db->Create_Transaction("tb_transactions", $_POST['id'], $_POST['date'], $_POST['time'], $_POST['name'], $_POST['status'])){
        echo "Transaction Recorded";
    } else echo "Failed to Record Transaction";
} else echo "Error: Database connection";
?>
