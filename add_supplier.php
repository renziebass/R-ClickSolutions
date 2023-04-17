<?php
require "DataBase.php";
$db = new DataBase();
if (isset($_POST['id'], $_POST['name'], $_POST['address'], $_POST['notes'], $_POST['mobile'])) {
    if ($db->dbConnect()) {
        if ($db->add_supplier($_POST['id'], $_POST['name'], $_POST['address'], $_POST['notes'], $_POST['mobile'])) {
            echo "Supplier Added!";
        } else echo "Supplier Already Exist";
    } else echo "Error: Database connection";
} else echo "All fields are required";
?>
