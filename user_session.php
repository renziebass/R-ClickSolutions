<?php
   require('config.php');
   session_start();
   $user_check = $_SESSION['id'];
   $sql1 = "SELECT tb_accounts.userid, tb_admins.first_name
   FROM tb_accounts
   JOIN tb_admins ON tb_accounts.userid=tb_admins.userid
   WHERE tb_accounts.userid = '$user_check'";
   $result1 = mysqli_query($db,$sql1);
   $row1 = mysqli_fetch_assoc($result1);
   $login_session = $row1['userid'];
   $first_name = $row1['first_name'];

   if(!isset($_SESSION['id'])){
      header("location:index.php");
      die();
   }
?>