<?php
require "DataBase.php";
$db = new DataBase();
if (isset($_POST['brand'])) {
    if ($db->dbConnect()) {
        if ($db->add_mcbrand($_POST['brand'])) {
            echo "Brand Added!";
        } else echo "Brand Already Exist";
    } else echo "Error: Database connection";
} else echo "All fields are required";
?>
