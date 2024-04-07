<?php
require "DataBase.php";
$db = new DataBase();
if ($db->dbConnect()) {
    if ($db->delete_product($_POST['Tid'], $_POST['Pid'], $_POST['quantity'])){
        echo "Product Returned";
    } else echo "Returning Failed";
} else echo "Error: Database connection";
?>
