<?php
require "DataBase.php";
$db = new DataBase();
if (isset($_POST['category'])) {
    if ($db->dbConnect()) {
        if ($db->add_category($_POST['category'])) {
            echo "Category Added!";
        } else echo "Category Already Exist";
    } else echo "Error: Database connection";
} else echo "All fields are required";
?>
