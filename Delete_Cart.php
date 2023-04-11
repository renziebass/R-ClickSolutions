<?php
require "DataBase.php";
$db = new DataBase();
if ($db->dbConnect()) {
    if ($db->Delete_Cart($_POST['transaction_id'])){
        echo "Transaction Deleted";
    } else echo "Failed to Delete Transaction";
} else echo "Error: Database connection";
?>
