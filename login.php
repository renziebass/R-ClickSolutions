<?php
require "DataBase.php";
$db = new DataBase();
if (isset($_POST['userid']) && isset($_POST['password'])) {
    if ($db->dbConnect()) {
        if ($db->logIn("tb_accounts", $_POST['userid'], $_POST['password'])) {
            echo "Login Success";
        } else echo "userid or Password wrong";
    } else echo "Error: Database connection";
} else echo "All fields are required";
?>
