<?php
require "DataBase.php";
$db = new DataBase();
if ($db->dbConnect()) {
    if ($db->Create_Cart("tb_cart", $_POST['transaction_id'], $_POST['date'], $_POST['product_id'], $_POST['quantity'], $_POST['price'])){
        echo "Added to Cart";
    } else echo "Failed to Add Cart";
} else echo "Error: Database connection";
?>
