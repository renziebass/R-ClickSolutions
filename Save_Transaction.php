<?php
require "DataBase.php";
$db = new DataBase();
if ($db->dbConnect()) {
    if ($db->Save_Transaction($_POST['id'], $_POST['name'])){
        echo "Transaction Saved";
    } else echo "Failed to Save Transaction";
} else echo "Error: Database connection";
?>
