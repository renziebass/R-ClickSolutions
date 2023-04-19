<?php
   
   require('config.php');
   session_start();
   
   $user_check = $_SESSION['id'];

    
   $query = "SELECT userid FROM tb_accounts WHERE userid = '$user_check'";
   
   $result = mysqli_query($db,$query);
   $row = mysqli_fetch_assoc($result);
   
   $login_session = $row['userid'];
   
   if(!isset($_SESSION['id'])){
      header("location:signin.php");
      die();
   }

  

?>