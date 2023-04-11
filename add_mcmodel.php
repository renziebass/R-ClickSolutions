<?php
require "DataBase.php";
$db = new DataBase();
if (isset($_POST['brand']) && isset($_POST['model'])) {
    if ($db->dbConnect()) {
        if ($db->add_mcmodel($_POST['brand'], $_POST['model'])) {
            echo "Model Added!";
        } else echo "Model Already Exist";
    } else echo "Error: Database connection";
} else echo "All fields are required";
?>
