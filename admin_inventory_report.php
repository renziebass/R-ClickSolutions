<?php
date_default_timezone_set("Asia/Manila");
include('user_session.php');
$sql1 = "SELECT
COUNT(tb_products.id)AS products
FROM tb_products";
$result1=mysqli_query($db,$sql1);
$row1 = mysqli_fetch_assoc($result1);

$sql2 = "SELECT COUNT(tb_product_category.category) AS category FROM tb_product_category";
$result2=mysqli_query($db,$sql2);
$row2 = mysqli_fetch_assoc($result2);

?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

    <title>INVENTORY REPORT</title>

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
      <div class="m-3 bg-light rounded" style="min-height: 600px;">
        <!--A1-->
        <div class="row pt-3">
            <div class="col">
            
        <div class="px-3 input-group d-flex flex-row-reverse">    
              <button onclick="printDiv()" class=" btn btn-outline-secondary" type="submit"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer-fill" viewBox="0 0 16 16">
                <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2H5zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1z"/>
                <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2V7zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
              </svg> Print</button>
        </div>
            <script>
                      function printDiv() {
                      var printContents = document.getElementById("page").innerHTML;
                      var originalContents = document.body.innerHTML;
                      document.body.innerHTML = printContents;
                      window.print();
                      document.body.innerHTML = originalContents;
                      }
                      function refreshDiv() {
                      location.reload();
                      } 
            </script>
        
            <div id="page">
      <table class="m-3 col-md-11 mx-auto table table-hover" id="saleshistorydiv">
                <thead >
                <h5 class="text-center" id="text1">INVENTORY REPORT</h5>
                <div class="col-md-8 container mx-auto">
                    <div class="row">
                      <div class="col-sm text-center mb-0 font-weight-bold">
                      <?php echo $row1['products']; ?>
                      </div>
                      <div class="col-sm text-center mb-0 font-weight-bold">
                      <?php echo $row2['category']; ?>
                      </div>
                      <div class="col-sm text-center mb-0 font-weight-bold">
                      <?php echo date("Y-m-d H:i") ?>
                      </div>
                    </div>
                </div>
                <div class="col-md-8 container mx-auto">
                    <div class="row">
                      <div class="col-sm text-center mt-0 text-muted">
                        Total Products
                      </div>
                      <div class="col-sm text-center text-muted">
                        Total Product Category
                      </div>
                      <div class="col-sm text-center text-muted">
                        Date & Time
                      </div>
                    </div>
                </div>
                
                  <tr class="text-muted">
                    <th scope="col">Product ID</th>
                    <th scope="col">Supplier ID</th>
                    <th scope="col">Product Brand</th>
                    <th scope="col">Category</th>
                    <th scope="col">MC Brand</th>
                    <th scope="col">MC Model</th>
                    <th scope="col">Stocks</th>
                    <th scope="col">Available</th>
                  </tr>
                </thead>
                <tbody>
                  
                <?php
                  $sql="SELECT
                  *
                  FROM tb_products
                  ORDER BY tb_products.date DESC";
                                                      
                  $result = mysqli_query($db,$sql);
      
                  if (mysqli_num_rows($result) > 0) 
                  {
                  foreach($result as $items)
                  {
                ?>
                      <tr>  
                      <td><?php echo $items['id']; ?></td>
                      <td><?php echo $items['supplier_id']; ?></td>
                      <td><?php echo $items['product_brand']; ?></td>
                      <td><?php echo $items['category']; ?></td>
                      <td><?php echo $items['mc_brand']; ?></td>
                      <td><?php echo $items['mc_model']; ?></td>
                      <td><?php echo $items['stocks']; ?></td>
                      <td><?php echo $items['available']; ?></td>
                      </tr>
                <?php
                  } 
                } 
                  ?>
                </tbody>
      </table> 
      </div>

            </div>
        </div>
        
        <!--A2-->
        
        
    

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