<?php
require "DataBase.php";
$db = new DataBase();
if ($db->dbConnect()) {
    if ($db->Paid_Transaction($_POST['id'])){
        echo "Payment Successful";
    } else echo "Failed to Record Transaction";
} else echo "Error: Database connection";
?>
