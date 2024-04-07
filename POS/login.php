<?php
require('config_app.php');

$userid = $_POST['userid'];
$password = $_POST['password'];

$sql = "SELECT *
FROM tb_accounts
WHERE tb_accounts.userid='$userid' AND tb_accounts.password='$password'";

$res = mysqli_query($con,$sql);
 
if (mysqli_num_rows($res) != 0) {
    echo "Login Success";
} else {
    echo "Login Failed";
}

?>
