<?php

$h5=null;

include('user_session.php');
$sql1 = "SELECT DATE_FORMAT(tb_payments.date,'%M %d,%Y') AS date1,tb_payments.date FROM tb_payments GROUP BY tb_payments.date DESC";
$result1=mysqli_query($db,$sql1);

$sql2 = "SELECT
CONCAT(FORMAT(SUM(tb_cart.price), 2)) AS income_today,
(SELECT COUNT(tb_payments.id)
FROM tb_payments WHERE tb_payments.date='".$_GET['date']."') AS customers,
(SELECT COUNT(tb_cart.product_id)) AS items
FROM tb_transactions
LEFT JOIN tb_payments ON tb_transactions.id=tb_payments.id
RIGHT JOIN tb_cart ON tb_transactions.id=tb_cart.transaction_id
WHERE tb_transactions.status='paid' AND tb_transactions.date='".$_GET['date']."'";
$result2=mysqli_query($db,$sql2);
$row2 = mysqli_fetch_assoc($result2);

$sql3 = "SELECT DATE_FORMAT(tb_payments.date,'%M %d,%Y') AS date1
FROM tb_payments WHERE tb_payments.date='". $_GET['date'] . "'";
$result3=mysqli_query($db,$sql3);
$row3 = mysqli_fetch_assoc($result3);

if(empty($row3)) {
  $h5 = "No Transactions on this Date";
} else {
  $h5 = "Paid Transactions of ".$row3['date1']."";
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

    <title>PAID TRANSACTIONS</title>

    
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
        <div class="col-md-11 input-group mb-5 mx-auto">
          <div class="input-group-prepend">
              <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar3" viewBox="0 0 16 16">
              <path d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857V3.857z"/>
              <path d="M6.5 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
            </svg> Select Date</button>
              <div class="dropdown-menu">
                <?php while($row1 = mysqli_fetch_array($result1)):;?>
                  <a class="dropdown-item" href="admin_paid.php?date=<?php echo $row1['date'];?>" value="<?php echo $row1['date'];?>">
                  <?php echo $row1['date1'];?></a>
                <?php endwhile; ?>
              </div>
              <button class="btn btn-outline-secondary" type="button"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-qr-code-scan" viewBox="0 0 16 16">
                <path d="M0 .5A.5.5 0 0 1 .5 0h3a.5.5 0 0 1 0 1H1v2.5a.5.5 0 0 1-1 0v-3Zm12 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0V1h-2.5a.5.5 0 0 1-.5-.5ZM.5 12a.5.5 0 0 1 .5.5V15h2.5a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 1 .5-.5Zm15 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1 0-1H15v-2.5a.5.5 0 0 1 .5-.5ZM4 4h1v1H4V4Z"/>
                <path d="M7 2H2v5h5V2ZM3 3h3v3H3V3Zm2 8H4v1h1v-1Z"/>
                <path d="M7 9H2v5h5V9Zm-4 1h3v3H3v-3Zm8-6h1v1h-1V4Z"/>
                <path d="M9 2h5v5H9V2Zm1 1v3h3V3h-3ZM8 8v2h1v1H8v1h2v-2h1v2h1v-1h2v-1h-3V8H8Zm2 2H9V9h1v1Zm4 2h-1v1h-2v1h3v-2Zm-4 2v-1H8v1h2Z"/>
                <path d="M12 9h2V8h-2v1Z"/>
              </svg> Scan</button>
            </div>
            <input type="text" class="form-control" aria-label="Text input with dropdown button" placeholder="Search Transaction ID">
            <div class="input-group-append" id="button-addon4">
              <button class="btn btn-outline-secondary" type="button"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
              <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
            </svg> Search</button>
              <button class="btn btn-outline-secondary" type="button" onclick="printDiv()"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer-fill" viewBox="0 0 16 16">
                <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2H5zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1z"/>
                <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2V7zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
              </svg>  Print</button>
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
            </div>
          </div>
        </div>
      </div>
      <div id="page">
      <table class="m-3 col-md-11 mx-auto table table-hover">
                
                <thead>
                <h5 class="text-center"><?php echo $h5?></h5>
                <div class="col-md-8 container mx-auto">
                    <div class="row">
                      <div class="col-sm text-center mb-0 font-weight-bold">
                      <?php echo $row2['customers']; ?>
                      </div>
                      <div class="col-sm text-center mb-0 font-weight-bold">
                      <?php echo $row2['items']; ?>
                      </div>
                      <div class="col-sm text-center mb-0 font-weight-bold">
                      <?php echo $row2['income_today']; ?>
                      </div>
                    </div>
                </div>
                <div class="col-md-8 container mx-auto">
                    <div class="row">
                      <div class="col-sm text-center mt-0 text-muted">
                        Customers
                      </div>
                      <div class="col-sm text-center text-muted">
                        Items Sold
                      </div>
                      <div class="col-sm text-center text-muted">
                        Total Sales Amount
                      </div>
                    </div>
                </div>
                  <tr>
                    <th scope="col">Transaction ID</th>
                    <th scope="col">Time</th>
                    <th scope="col">Items</th>
                    <th scope="col">Total</th>
                    <th scope="col">Payment</th>
                    <th scope="col">Change</th>
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
                            tb_payments.payment,
                            tb_payments.change1
                          FROM tb_payments
                        JOIN tb_cart ON tb_payments.id=tb_cart.transaction_id
                        WHERE tb_payments.date='". $_GET['date'] . "'
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
                      <td><a class="" onclick="location.href='admin_transaction.php?id=<?php echo $items['id'];?>'"><?php echo $items['id'];?></a></td>
                      <td><?php echo $items['time']; ?></td>
                      <td><?php echo $items['items']; ?></td>
                      <td><?php echo $items['total']; ?></td>
                      <td><?php echo $items['payment']; ?></td>
                      <td><?php echo $items['change1']; ?></td>
                      </tr>
                <?php
                  } 
                } 
                  ?>
                </tbody>
      </table>
      </div>
       
      
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