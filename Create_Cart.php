<?php

require('config_app.php');

$transaction_id = $_POST['transaction_id'];
$date = $_POST['date'];
$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'];
$price = $_POST['price'];
$total = $price * $quantity;

$sql1 = "INSERT INTO tb_cart (transaction_id, date, product_id,quantity,price,total) VALUES
('$transaction_id','$date','$product_id','$quantity','$price','$total')";

$sql2 ="UPDATE tb_products SET tb_products.available=tb_products.available-'$quantity'WHERE tb_products.id='$product_id'";


if ((mysqli_query($con, $sql1)) && (mysqli_query($con, $sql2))) {
    echo "Added to Cart";
} else echo "Failed to Add Cart";
?>
