<?php
date_default_timezone_set("Asia/Manila");
include('user_session.php');
$sql1 = "SELECT
tb_admins.userid,
tb_admins.first_name,
tb_admins.middle_name,
tb_admins.last_name,
tb_admins.phone,
tb_accounts.password
FROM tb_admins
JOIN tb_accounts ON tb_admins.userid=tb_accounts.userid
WHERE tb_admins.userid='$login_session'";
$result1=mysqli_query($db,$sql1);
$row1 = mysqli_fetch_assoc($result1);


function validateInput($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  
  if (empty($_POST["phone"])) {
    $phone_err = "* ";
  } else {
    $phone = validateInput($_POST["phone"]);
  }
  if (empty($_POST["password"])) {
    $password_err = "* ";
  } else {
    $password = validateInput($_POST["password"]);
  }
  
  if(!empty($_POST["password"]) && !empty($_POST["phone"]))
  {
    try {

        $sql2 = "UPDATE `tb_admins` 
        SET tb_admins.phone='$phone'
        WHERE tb_admins.userid='$login_session'";
        $UpdatePhone = mysqli_query($db, $sql2);
        $sql3 = "UPDATE `tb_accounts`
        SET tb_accounts.password='$password'
        WHERE tb_accounts.userid='$login_session'";
        $UpdatePassword = mysqli_query($db, $sql3);
        
        header("Location: admin_settings.php");
      
    }
    catch(PDOException $e)
      {
        echo $sql2 . "<br>" . $e->getMessage();
      }
    $db=null;
}
}
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

    <title>SETTINGS</title>

  </head>
  <body style="background-color: #c7c7c7;" onload = "navbar();">
  <script type="text/javascript">
      function navbar(){
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function(){
          document.getElementById("navbar").innerHTML = this.responseText;
        }
        xhttp.open("GET", "admin_navbar.php");
        xhttp.send();
      }
      setInterval(function(){
        navbar();
      }, 10000);
    </script>
    <div style="background-color: #ffc067;" id="navbar"></div><!--navbar -->
    <div class="mx-5">
      <div class="m-3 bg-light rounded">
      <div class="col">
      <h5 class="text-center pt-3" id="text1">ACCOUNT SETTINGS</h5>
          <div class="col p-5">
              <form method="post" action="" enctype="multipart/form-data">
              <div class="form-row">
                <div class="form-group col-md-1">
                  <input type="text" class="form-control" name="userid" value="<?php echo $row1['userid'];?>" disabled>
                </div>
                <div class="form-group col-md-2">
                  <input type="text" class="form-control" onkeyup="this.value = this.value.toUpperCase();" name="first_name" value="<?php echo $row1['first_name'];?>" disabled>
                </div>
                <div class="form-group col-md-2">
                  <input type="text" class="form-control" onkeyup="this.value = this.value.toUpperCase();" name="middle_name" value="<?php echo $row1['middle_name'];?>" disabled>
                </div>
                <div class="form-group col-md-2">
                  <input type="text" class="form-control" onkeyup="this.value = this.value.toUpperCase();" name="last_name" value="<?php echo $row1['last_name'];?>" disabled>
                </div>
                <div class="form-group col-md-2">
                  <input type="text" class="form-control" name="phone" value="<?php echo $row1['phone'];?>">
                </div>
                <div class="form-group col-md-3">
                  <input type="text" class="form-control" name="password" value="<?php echo $row1['password'];?>">
                </div>
              </div>
              <div class=" input-group d-flex flex-row-reverse">
              <button onclick="printDiv()" class="btn btn-outline-secondary" type="submit"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-database-add" viewBox="0 0 16 16">
                <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 0 1-1 0v-1h-1a.5.5 0 0 1 0-1h1v-1a.5.5 0 0 1 1 0Z"/>
                <path d="M12.096 6.223A4.92 4.92 0 0 0 13 5.698V7c0 .289-.213.654-.753 1.007a4.493 4.493 0 0 1 1.753.25V4c0-1.007-.875-1.755-1.904-2.223C11.022 1.289 9.573 1 8 1s-3.022.289-4.096.777C2.875 2.245 2 2.993 2 4v9c0 1.007.875 1.755 1.904 2.223C4.978 15.71 6.427 16 8 16c.536 0 1.058-.034 1.555-.097a4.525 4.525 0 0 1-.813-.927C8.5 14.992 8.252 15 8 15c-1.464 0-2.766-.27-3.682-.687C3.356 13.875 3 13.373 3 13v-1.302c.271.202.58.378.904.525C4.978 12.71 6.427 13 8 13h.027a4.552 4.552 0 0 1 0-1H8c-1.464 0-2.766-.27-3.682-.687C3.356 10.875 3 10.373 3 10V8.698c.271.202.58.378.904.525C4.978 9.71 6.427 10 8 10c.262 0 .52-.008.774-.024a4.525 4.525 0 0 1 1.102-1.132C9.298 8.944 8.666 9 8 9c-1.464 0-2.766-.27-3.682-.687C3.356 7.875 3 7.373 3 7V5.698c.271.202.58.378.904.525C4.978 6.711 6.427 7 8 7s3.022-.289 4.096-.777ZM3 4c0-.374.356-.875 1.318-1.313C5.234 2.271 6.536 2 8 2s2.766.27 3.682.687C12.644 3.125 13 3.627 13 4c0 .374-.356.875-1.318 1.313C10.766 5.729 9.464 6 8 6s-2.766-.27-3.682-.687C3.356 4.875 3 4.373 3 4Z"/>
              </svg> Update</button>
              </div>
              </form>
          </div>
          
      </div>
        <!--  -->
      </div><!-- last div-->
      <footer class="m-3 mt-4 text-muted border-top">
      <p>&copy; R-Click Solutions 2023</p>
      </footer>
    </div>
    

    <!--  -->

    <!-- Option 1: jQuery and Bootstrap Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>
    -->
  </body>
</html> 