<?php
include("config.php");
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
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
      .form-signin {
        width: 100%;
        max-width: 330px;
        padding: 15px;
        margin: auto;
        }
        .container-fluid {
            margin-top: 6em;
            margin-bottom: 10em;
        }
        .form-signin .checkbox {
        font-weight: 400;
      }

      .form-signin .form-floating:focus-within {
        z-index: 2;
        }

        .form-signin input[type="email"] {
          margin-bottom: -1px;
          border-bottom-right-radius: 0;
          border-bottom-left-radius: 0;
        }

        .form-signin input[type="password"] {
          margin-bottom: 10px;
          border-top-left-radius: 0;
          border-top-right-radius: 0;
        }
    </style>
    <title>POS SIGN IN</title>

    <body style="background-color: #c7c7c7;">
    <div style="background-color: #ffc067;" id="navbar">
    <nav class="navbar navbar-expand-lg navbar-light mx-5">
          <a class="navbar-brand font-weight-bold" href="#">R-Click POS</a>
          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          
    </nav>
  </div><!--navbar -->

    <div class="mx-5" >
    <div class="container-fluid">
<main class="form-signin rounded" style="background-color: #ffc067;">
  <form method="post">
    <img class="" src="CSB.png" alt="">
    <h1 class="h3 mb-3 fw-normal text-black">Sign-in</h1>

    <div class="form-floating">
      <input name="id" type="text" class="form-control" id="floatingInput" placeholder="User ID">
      
    </div>
    <div class="form-floating mt-1">
      <input  name="password" type="password" class="form-control" id="floatingPassword" placeholder="Password">
      
    </div>


    <button class="w-100 btn btn-md btn-secondary mt-3" type="submit">Sign in</button>
   
    
    <p class="text-white mt-3">
    <?php
        
        session_start();
                              
        if($_SERVER["REQUEST_METHOD"] == "POST") {
        // username and password sent from form 
        $id = mysqli_real_escape_string($db,$_POST['id']);
        $password = mysqli_real_escape_string($db,$_POST['password']);

        $sql1 = "SELECT * FROM tb_accounts WHERE userid='$id' AND password='$password'";
        $run_user = mysqli_query($db, $sql1);
        

        if (!$sql1) {
            die(mysqli_error($db));
        } 
        
        $check_user = mysqli_num_rows($run_user);
       


            // here you need $run_user data
            // use fetch_ methods, for example `fetch_assoc()`
            $user_data = mysqli_fetch_assoc($run_user);


      
            
            
              //redirect somehwere
              $_SESSION['id'] = $id;
              header("location: admin_home.php");
            
              
        }
    ?>
    </p>
    
  </form>
  
</main>
</div>
      <footer class="m-3 mt-4 text-muted border-top">
      <p>&copy; R-Click Solutions 2023</p>
      </footer>
    </div>
    

    <!-- Optional JavaScript; choose one of the two! -->

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