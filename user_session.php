<?php
   require('index.php');
   session_start();
   $user_check = $_SESSION['id'];
   $sql1 = "SELECT
   tb_accounts.userid,
   CONCAT(tb_users.first_name,' ',tb_users.last_name) AS name
   FROM tb_accounts
   JOIN tb_users ON tb_accounts.userid=tb_users.userid
   WHERE tb_accounts.userid='$user_check'";
   $result1 = mysqli_query($db,$sql1);
   $row1 = mysqli_fetch_assoc($result1);
   $login_session = $row1['userid'];
   $name=$row1['name'];

   if(!isset($_SESSION['id'])){
      header("location:index.php");
      die();
   }
?>