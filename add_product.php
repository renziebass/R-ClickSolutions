<?php
require "DataBase.php";
$db = new DataBase();
    if ($db->dbConnect()) {
        if ($db->add_product($_POST['id'], $_POST['product_brand'], $_POST['category'], $_POST['mc_brand'], $_POST['mc_model'], $_POST['stocks'], $_POST['available'], $_POST['price'], $_POST['supplier_id'])) {
            echo "Product Successfuly Added!";
        } else echo "Product Already Exist";
    } else echo "Error: Database connection";
?>
