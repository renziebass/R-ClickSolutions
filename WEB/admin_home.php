<?php
include('user_session.php');
$sql1="SELECT
SUM(tb_payments.total) AS paid
FROM tb_transactions
LEFT JOIN tb_payments ON tb_transactions.id=tb_payments.id
WHERE tb_transactions.status='paid' AND tb_transactions.date='".date("Y-m-d")."'";
$result1=mysqli_query($db,$sql1);
$row1 = mysqli_fetch_assoc($result1);

$sql2="SELECT
SUM(tb_cart.price) AS unpaid
FROM tb_transactions
LEFT JOIN tb_cart ON tb_transactions.id=tb_cart.transaction_id
WHERE tb_transactions.status='unpaid' AND tb_transactions.date='".date("Y-m-d")."'";
$result2=mysqli_query($db,$sql2);
$row2 = mysqli_fetch_assoc($result2);

$currentsales=$row1['paid']+$row2['unpaid'];

$sql3="SELECT
COUNT(tb_transactions.id) as paidcustomers
FROM tb_transactions
WHERE tb_transactions.status='paid' AND tb_transactions.date='".date("Y-m-d")."'";
$result3=mysqli_query($db,$sql3);
$row3 = mysqli_fetch_assoc($result3);

$sql4="SELECT
COUNT(tb_transactions.id) as unpaidcustomers
FROM tb_transactions
WHERE tb_transactions.status='unpaid' AND tb_transactions.date='".date("Y-m-d")."'";
$result4=mysqli_query($db,$sql4);
$row4 = mysqli_fetch_assoc($result4);

$currentcustomers=$row3['paidcustomers']+$row4['unpaidcustomers'];

$sql5="SELECT
tb_transactions.date,
SUM(tb_payments.total) AS sales,
SUM(tb_cart.price) AS unpaid
FROM tb_transactions
JOIN tb_payments ON tb_transactions.id=tb_payments.id
JOIN tb_cart ON tb_transactions.id=tb_cart.transaction_id
WHERE tb_transactions.date NOT IN (SELECT tb_transactions.date FROM tb_transactions WHERE tb_transactions.date='".date("Y-m-d")."')
GROUP BY tb_transactions.date
ORDER BY tb_transactions.date DESC
LIMIT 5";
$result5=mysqli_query($db,$sql5);


?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

    <title>DASHBOARD</title>

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load("current", {packages:["corechart","bar"]});
      google.charts.setOnLoadCallback(drawChart1);

      function drawChart1() {
        ///chart1
        var data1 = google.visualization.arrayToDataTable([
          ['Task', 'Hours per Day'],
          ['PAID',     <?php echo $row1['paid']; ?>],
          ['UNPAID',   <?php echo $row2['unpaid']; ?>],
        ]);
        var options1 = {
          title: 'CURRENT SALES : <?php echo $currentsales; ?>',
        };
        var chart = new google.visualization.PieChart(document.getElementById('chart1'));
        chart.draw(data1, options1);
        ///chart2
        var data2 = google.visualization.arrayToDataTable([
          ['Task', 'Hours per Day'],
          ['PAID',     <?php echo $row3['paidcustomers']; ?>],
          ['UNPAID',      <?php echo $row4['unpaidcustomers']; ?>],
        ]);
        var options2 = {
          title: 'CUSTOMERS: <?php echo $currentcustomers; ?>',
        };
        var chart = new google.visualization.PieChart(document.getElementById('chart2'));
        chart.draw(data2, options2);
        ///chart3
      
        ///chart4
        var data4 = google.visualization.arrayToDataTable([
          ['Year', 'Sales', 'Expenses'],
          <?php while($row5 = mysqli_fetch_array($result5)):;?>
          ['<?php echo $row5['date'];?>', <?php echo $row5['sales'];?>, <?php echo $row5['unpaid'];?>],
          <?php endwhile; ?>
        ]);
        var options4 = {
          chart: {
            title: 'Previous 5 Days Financial Report',
            subtitle: 'Sales & Expenses',
          }
        };
        var chart = new google.charts.Bar(document.getElementById('chart7'));
        chart.draw(data4, google.charts.Bar.convertOptions(options4));
        //chart5
        var data5 = google.visualization.arrayToDataTable([
          ['Year', 'Sales', 'Expenses'],
          ['FEBRUARY 2023', 1000, 400],
          ['JANUARY 2023', 1170, 460],
          ['DECEMBER 2022', 660, 1120],
          ['NOVEMBER 2022', 1030, 540]
        ]);
        var options5 = {
          chart: {
            title: 'Monthly Financial Report',
            subtitle: 'Sales & Expenses',
          }
        };
        var chart = new google.charts.Bar(document.getElementById('chart8'));
        chart.draw(data5, google.charts.Bar.convertOptions(options5));
      }
    </script>
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
      <div class="m-3 bg-white rounded">
      <div class="row px-3 mt-0" style="height: 200px;">
            <div class="col p-0" id="chart1"></div>
            <div class="col p-0" id="chart2"></div>
            <div class="col p-0" >
            <table style="width:100%;">
                
                <thead>
                <h5 class="text-center mt-2">Recent Paid Transactions</h5>
                  <tr>
                    <th scope="col" style="padding-left: 5px; padding-right: 5px;">Transaction ID</th>
                    <th scope="col" style="padding-left: 5px; padding-right: 5px;">Time</th>
                    <th scope="col" style="padding-left: 5px; padding-right: 5px;">Items</th>
                    <th scope="col" style="padding-left: 5px; padding-right: 5px;">Total</th>
                    <th scope="col" style="padding-left: 5px; padding-right: 5px;">Change</th>
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
                  ORDER BY A.time DESC
                  LIMIT 4";
                                                      
                  $result = mysqli_query($db,$sql);
      
                  if (mysqli_num_rows($result) > 0) 
                  {
                  foreach($result as $items)
                  {
                ?>
                      <tr>  
                      <td style="padding-left: 5px; padding-right: 5px;"><?php echo $items['id']; ?></td>
                      <td style="padding-left: 5px; padding-right: 5px;"><?php echo $items['time']; ?></td>
                      <td style="padding-left: 5px; padding-right: 5px;"><?php echo $items['items']; ?></td>
                      <td style="padding-left: 5px; padding-right: 5px;"><?php echo $items['total']; ?></td>
                      <td style="padding-left: 5px; padding-right: 5px;"><?php echo $items['change1']; ?></td>
                      </tr>
                <?php
                  } 
                } 
                  ?>
                </tbody>
              </table> 
            </div>
          </div>
          <div class="row px-3 mt-0">
            <div class="col p-0" id="chart4"></div>
            <div class="col p-0" id="chart5"></div>
            <div class="col p-0" id="chart6"></div>
          </div>
          <div class="row px-3 mt-0" style="height: 250px;">
            <div class="col p-0" id="chart7"></div>
            <div class="col p-0" id="chart8"></div>
            <div class="col p-0 bg-white" >
            <table style="width:100%;">
                
                <thead>
                <h5 class="text-center mt-2">Recent Unpaid Transactions</h5>
                  <tr>
                    <th scope="col" style="padding-left: 10px; padding-right: 10px;">LastName</th>
                    <th scope="col" style="padding-left: 10px; padding-right: 10px;">Time</th>
                    <th scope="col" style="padding-left: 10px; padding-right: 10px;">Items</th>
                    <th scope="col" style="padding-left: 10px; padding-right: 10px;">Total</th>
                  </tr>
                </thead>
                <tbody>
                <?php
                  $sql="SELECT
                  tb_transactions.name,
                  tb_transactions.time,
                  SUM(tb_cart.quantity) AS items,
                  SUM(tb_cart.total) AS total
                  FROM tb_transactions
                  INNER JOIN tb_cart ON tb_transactions.id=tb_cart.transaction_id
                  WHERE tb_transactions.status='unpaid'
                  AND tb_cart.date='".date("Y-m-d")."'
                  LIMIT 3;";
                                                      
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
          <div class="row px-3 mt-0" style="height: 160px;">
            <div class="col p-0" style="background-color: #F6D1CB;">
            <table style="width:100%;">
                
                <thead>
                <h5 class="text-center">Zero Stocks</h5>
                  <tr>
                    <th scope="col" style="padding-left: 10px; padding-right: 10px;">Product ID</th>
                    <th scope="col" style="padding-left: 10px; padding-right: 10px;">Brand</th>
                    <th scope="col" style="padding-left: 10px; padding-right: 10px;">Specification</th>
                    <th scope="col" style="padding-left: 10px; padding-right: 10px;">Available</th>
                  </tr>
                </thead>
                <tbody>
                <?php
                  $sql="SELECT
                  tb_products.id,
                  tb_products.product_brand,
                  CONCAT(tb_products.category,'-',tb_products.mc_model) AS specification,
                  tb_products.available
                  FROM
                  tb_products
                  WHERE tb_products.available='0'
                  LIMIT 3";
                                                      
                  $result = mysqli_query($db,$sql);
      
                  if (mysqli_num_rows($result) > 0) 
                  {
                  foreach($result as $items)
                  {
                ?>
                      <tr>  
                      <td style="padding-left: 10px; padding-right: 10px;"><?php echo $items['id']; ?></td>
                      <td style="padding-left: 10px; padding-right: 10px;"><?php echo $items['product_brand']; ?></td>
                      <td style="padding-left: 10px; padding-right: 10px;"><?php echo $items['specification']; ?></td>
                      <td style="padding-left: 10px; padding-right: 10px;"><?php echo $items['available']; ?></td>
                      </tr>
                <?php
                  } 
                } 
                  ?>
                </tbody>
              </table> 
            </div>
            <div class="col p-0" style="background-color: #DBF9F4;">
            <table style="width:100%;">
                
                <thead>
                <h5 class="text-center">Low on Stocks</h5>
                  <tr>
                  <th scope="col" style="padding-left: 10px; padding-right: 10px;">Product ID</th>
                    <th scope="col" style="padding-left: 10px; padding-right: 10px;">Brand</th>
                    <th scope="col" style="padding-left: 10px; padding-right: 10px;">Specification</th>
                    <th scope="col" style="padding-left: 10px; padding-right: 10px;">Available</th>
                  </tr>
                </thead>
                <tbody>
                <?php
                  $sql="SELECT
                  tb_products.id,
                  tb_products.product_brand,
                  CONCAT(tb_products.category,'-',tb_products.mc_model) AS specification,
                  tb_products.available
                  FROM
                  tb_products
                  WHERE tb_products.available <= 5 AND tb_products.available
                  NOT IN (SELECT tb_products.available FROM tb_products WHERE tb_products.available='0') 
                  LIMIT 3";
                                                      
                  $result = mysqli_query($db,$sql);
      
                  if (mysqli_num_rows($result) > 0) 
                  {
                  foreach($result as $items)
                  {
                ?>
                      <tr>  
                      <td style="padding-left: 10px; padding-right: 10px;"><?php echo $items['id']; ?></td>
                      <td style="padding-left: 10px; padding-right: 10px;"><?php echo $items['product_brand']; ?></td>
                      <td style="padding-left: 10px; padding-right: 10px;"><?php echo $items['specification']; ?></td>
                      <td style="padding-left: 10px; padding-right: 10px;"><?php echo $items['available']; ?></td>
                      </tr>
                <?php
                  } 
                } 
                  ?>
                </tbody>
              </table> 
            </div>
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