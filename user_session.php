<?php
   require('config.php');
   session_start();
   $user_check = $_SESSION['id'];
   $sql1 = "SELECT
   tb_accounts.userid,
   CONCAT(tb_users.first_name,' ',tb_users.last_name) AS name
   FROM tb_accounts
   JOIN tb_users ON tb_accounts.userid=tb_users.userid
   WHERE tb_accounts.userid='$user_check'";
   $result1 = mysqli_query($db1,$sql1);
   $row1 = mysqli_fetch_assoc($result1);

   $login_session = $row1['userid'];
   $name=$row1['name'];

   /////////////////////////////////////////////////////////////////////

   $sql2="SELECT
   tb_accounts.user,
   tb_accounts.pass,
   tb_accounts.db
   tb_users.company_id
   FROM tb_accounts
   LEFT JOIN tb_users
   ON tb_accounts.id=tb_users.company_id
   WHERE tb_users.user_id";
   $result2 = mysqli_query($db,$sql2);
   $row2 = mysqli_fetch_assoc($result2);


   define('HOST1','localhost');
   define('USER1',$row2['user']);
   define('PASS1',$row1['pass']);
   define('DB1',$row1['db']);

   $db = mysqli_connect(HOST1,USER1,PASS1,DB1);
   $con = mysqli_connect(HOST1,USER1,PASS1,DB1);

   if(!isset($_SESSION['id'])){
      header("location:index.php");
      die();
   }
?>