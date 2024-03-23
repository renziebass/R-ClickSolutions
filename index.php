<?php
include("config.php");
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>R-Click Solutions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }

      .b-example-divider {
        width: 100%;
        height: 3rem;
        background-color: rgba(0, 0, 0, .1);
        border: solid rgba(0, 0, 0, .15);
        border-width: 1px 0;
        box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
      }

      .b-example-vr {
        flex-shrink: 0;
        width: 1.5rem;
        height: 100vh;
      }

      .bi {
        vertical-align: -.125em;
        fill: currentColor;
      }

      .nav-scroller {
        position: relative;
        z-index: 2;
        height: 2.75rem;
        overflow-y: hidden;
      }

      .nav-scroller .nav {
        display: flex;
        flex-wrap: nowrap;
        padding-bottom: 1rem;
        margin-top: -1px;
        overflow-x: auto;
        text-align: center;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
      }

      .btn-bd-primary {
        --bd-violet-bg: #712cf9;
        --bd-violet-rgb: 112.520718, 44.062154, 249.437846;

        --bs-btn-font-weight: 600;
        --bs-btn-color: var(--bs-white);
        --bs-btn-bg: var(--bd-violet-bg);
        --bs-btn-border-color: var(--bd-violet-bg);
        --bs-btn-hover-color: var(--bs-white);
        --bs-btn-hover-bg: #6528e0;
        --bs-btn-hover-border-color: #6528e0;
        --bs-btn-focus-shadow-rgb: var(--bd-violet-rgb);
        --bs-btn-active-color: var(--bs-btn-hover-color);
        --bs-btn-active-bg: #5a23c8;
        --bs-btn-active-border-color: #5a23c8;
      }
      .bd-mode-toggle {
        z-index: 1500;
      }
    </style>
    <link href="sign-in.css" rel="stylesheet">
</head>
  <body class="bg-image" 
     style="background-image: url('https://rclickpos.com/assets/img/BG2.jpg');
            height: 100vh">
  
      
    <main class="form-signin w-100 m-auto">
    <div class="container text-center">
    <img class="shadow"src="assets/img/R-ClickLogo.png" alt="" width="100" height="100">
  
    </div>
    <form method="post">
    <h1 class="h3 mb-3 fw-normal text-center">R-Click Solutions POS</h1>

    <div class="form-floating py-1">
      <input type="text" class="form-control" name="id" id="id">
      <label for="id">User ID</label>
    </div>
    <div class="form-floating py-1">
      <input type="password" class="form-control" name="password" id="password">
      <label for="password">Password</label>
      <p class="text-danger mt-3 text-center">
    <?php
        session_start();
        if($_SERVER["REQUEST_METHOD"] == "POST") {
        $id = mysqli_real_escape_string($db1,$_POST['id']);
        $password = mysqli_real_escape_string($db1,$_POST['password']);

        $sql0 = "SELECT
        tb_accounts.user,
        tb_accounts.pass,
        tb_accounts.db,
        tb_users.user_id,
        tb_users.company_id
        FROM tb_accounts
        LEFT JOIN tb_users
        ON tb_accounts.id=tb_users.company_id
        WHERE tb_users.user_id='$id'";
        $run_user0 = mysqli_query($db1, $sql0);
        $row0 = mysqli_fetch_assoc($run_user0);

        if (!$sql0) {
          die(mysqli_error($db1));
        }
        $check_user0 = mysqli_num_rows($run_user0);
        if($check_user0>0){

          define('HOST1','localhost');
          define('USER1',$row0['user']);
          define('PASS1',$row0['pass']);
          define('DB1',$row0['db']);

          /*
          define('HOST1','localhost');
          define('USER1',$row0['user']);
          define('PASS1',$row0['pass']);
          define('DB1',$row0['db']);

          define('HOST1','localhost');
          define('USER1','root');
          define('PASS1','');
          define('DB1','timonio001');

          define('HOST1','localhost');
          define('USER1','root');
          define('PASS1','');
          define('DB1','kg_db');
          */


          $db2 = mysqli_connect(HOST1,USER1,PASS1,DB1);
  

          $sql1 = "SELECT * FROM tb_accounts WHERE userid='$id' AND password='$password'";
          $run_user = mysqli_query($db2, $sql1);

          if (!$sql1) {
              die(mysqli_error($db2));
          }  

          $check_user = mysqli_num_rows($run_user);
          if($check_user>0){
            $user_data = mysqli_fetch_assoc($run_user);
          
            $sql2 = "INSERT INTO `tb_login_history` (`id`, `date`, `time`)
            VALUES ('$id','".date("Y-m-d")."','".date("H:i:s")."')";

            if ($user_data["acc_type"] == 'CASHIER') {
              //redirect somehwere
              $_SESSION['id'] = $id;
              $recordSession = mysqli_query($db2, $sql2);
              header("location: cashier_dashboard.php");
            }
            if ($user_data["acc_type"] == 'ADMIN') {
              //redirect somehwere
              $_SESSION['id'] = $id;
              $recordSession = mysqli_query($db2, $sql2);
              header("location: admin_dashboard.php");
            }
          } else {
            echo $error = "Login Credentials is invalid";
          }
          
        } else {
          echo $error = "Invalid User ID";
        }  

        
    }
    ?>
    </p>
    </div>
    <div class="checkbox mb-3 text-center">
      <label>
        <input type="checkbox" value="remember-me"> Remember me
      </label>
    </div>
    <button class="w-100 btn btn-sm btn-primary" type="submit">Sign in</button>
    <p class="mt-5 mb-3 text-body-secondary text-center">&copy; R-Click Solutions PH 2023</p>
  </form>
</main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
  </body>
</html>