<?php
require "DataBase.php";
$db = new DataBase();
if ($db->dbConnect()) {
    if ($db->Confirm_Payment("tb_payments", $_POST['id'], $_POST['date'], $_POST['time'], $_POST['total'], $_POST['change'])){
        echo "Payment Confirmed";
    } else echo "Failed to Record Transaction";
} else echo "Error: Database connection";
?>
