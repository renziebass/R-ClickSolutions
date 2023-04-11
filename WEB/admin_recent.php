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

    <title>RECECNT TRANSACTIONS</title>

  
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
      
      <div class="row  mt-0" style="height:600px;">
            <div class="col-md-6">
            <table style="width:100%;" class="mb-3">
                
                <thead>
                <h5 class="text-center mt-3">Recent Paid Transactions</h5>
                  <tr>
                    <th scope="col" style="padding-left: 10px; padding-right: 10px;">Transaction ID</th>
                    <th scope="col" style="padding-left: 10px; padding-right: 10px;">Time</th>
                    <th scope="col" style="padding-left: 10px; padding-right: 10px;">Items</th>
                    <th scope="col" style="padding-left: 10px; padding-right: 10px;">Total</th>
                    <th scope="col" style="padding-left: 10px; padding-right: 10px;">Change</th>
                  </tr>
                </thead>
                <tbody>
                <?php
                  $sql="SELECT *
                  FROM (SELECT
                          tb_transactions.id,
                          tb_transactions.time,
                          tb_transactions.date
                          FROM tb_transactions WHERE tb_transactions.status='paid') AS A
                  JOIN (SELECT
                          tb_payments.id,
                          COUNT(tb_cart.transaction_id) as items,
                            SUM(tb_cart.price) AS total,
                            tb_payments.change1
                          FROM tb_payments
                        JOIN tb_cart ON tb_payments.id=tb_cart.transaction_id
                        WHERE tb_payments.date='".date("Y-m-d")."'
                          GROUP BY tb_payments.id) AS B
                  ON A.id=B.id
                  GROUP BY A.id
                  ORDER BY A.time DESC";
                                                      
                  $result = mysqli_query($db,$sql);
      
                  if (mysqli_num_rows($result) > 0) 
                  {
                  foreach($result as $items)
                  {
                ?>
                      <tr>  
                      <td style="padding-left: 10px; padding-right: 10px;"><?php echo $items['id']; ?></td>
                      <td style="padding-left: 10px; padding-right: 10px;"><?php echo $items['time']; ?></td>
                      <td style="padding-left: 10px; padding-right: 10px;"><?php echo $items['items']; ?></td>
                      <td style="padding-left: 10px; padding-right: 10px;"><?php echo $items['total']; ?></td>
                      <td style="padding-left: 10px; padding-right: 10px;"><?php echo $items['change1']; ?></td>
                      </tr>
                <?php
                  } 
                } 
                  ?>
                </tbody>
              </table> 
            </div>
            <div class="col-md-6" style="background-color:#F6D1CB;">
            <table style="width:100%;" class="mb-3">
                
                <thead>
                <h5 class="text-center mt-3">Recent Unpaid Transactions</h5>
                  <tr>
                  <th scope="col" style="padding-left: 10px; padding-right: 10px;">LastName</th>
                    <th scope="col" style="padding-left: 10px; padding-right: 10px;">Time</th>
                    <th scope="col" style="padding-left: 10px; padding-right: 10px;">Items</th>
                    <th scope="col" style="padding-left: 10px; padding-right: 10px;">Total</th>
                  </tr>
                </thead>
                <tbody>
                <?php
                  $sql="SELECT tb_transactions.name, tb_transactions.time, COUNT(tb_cart.transaction_id) as items,
                  SUM(tb_cart.price) AS total
                  FROM tb_transactions
                  INNER JOIN tb_cart ON tb_transactions.id=tb_cart.transaction_id
                  WHERE tb_transactions.status='unpaid'
                  AND tb_transactions.date='".date("Y-m-d")."'
                  GROUP BY tb_transactions.id";
                                                      
                  $result = mysqli_query($db,$sql);
      
                  if (mysqli_num_rows($result) > 0) 
                  {
                  foreach($result as $items)
                  {
                ?>
                      <tr>  
                      <td style="padding-left: 10px; padding-right: 10px;"><?php echo $items['name']; ?></td>
                      <td style="padding-left: 10px; padding-right: 10px;"><?php echo $items['time']; ?></td>
                      <td style="padding-left: 10px; padding-right: 10px;"><?php echo $items['items']; ?></td>
                      <td style="padding-left: 10px; padding-right: 10px;"><?php echo $items['total']; ?></td>
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