<?php
include('user_session.php');

?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

    <title>MANAGE USERS</title>

    
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
      <div class="m-3 pb-3 bg-light rounded" style="min-height: 600px;">
      <div class="row pt-3">
        <div class="col">
        <div class="col-md-6 input-group mb-3 mx-auto">
          <div class="input-group-prepend">
            
              <button class="btn btn-outline-secondary" type="button" onclick="window.open('admin_addproduct.php')">Add</button>
            </div>
            <input type="text" class="form-control" aria-label="Text input with dropdown button" placeholder="Search USER ID">
            <div class="input-group-append" id="button-addon4">
              <button class="btn btn-outline-secondary" type="button">Search</button>
            </div>
          </div>
        </div>
      </div>
      
      <table class="m-3 col-md-6 mx-auto table table-hover">
                
                <thead>
                <div class="col-md-8 container mx-auto">
                    <div class="row">
                      <div class="col-sm text-center mb-0 font-weight-bold">
                    3
                      </div>
                      
                    </div>
                </div>
                <div class="col-md-8 container mx-auto">
                    <div class="row">
                      <div class="col-sm text-center text-muted">
                        Cashiers
                      </div>
                    </div>
                </div>
                  <tr>
                    <th scope="col">USER ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Phone #</th>
                  </tr>
                </thead>
                <tbody>
                  
                <?php
                  $sql="SELECT
                  tb_cashiers.userid,
                  CONCAT(tb_cashiers.last_name,', ',tb_cashiers.first_name,' ',tb_cashiers.middle_name) AS name,
                  tb_cashiers.phone
                  FROM tb_cashiers";
                                                      
                  $result = mysqli_query($db,$sql);
      
                  if (mysqli_num_rows($result) > 0) 
                  {
                  foreach($result as $items)
                  {
                ?>
                      <tr>  
                      <td><a class="" onclick="location.href='admin_product_manage.php?id=<?php echo $items['userid'];?>'"><?php echo $items['userid'];?></a></td>
                      <td><?php echo $items['name']; ?></td>
                      <td><?php echo $items['phone']; ?></td>
                      </tr>
                <?php
                  } 
                } 
                  ?>
                </tbody>
              </table> 
      
      </div><!-- last div-->
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