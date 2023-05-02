<?php
include('user_session.php');
$sql1="SELECT
CONCAT(FORMAT(SUM(tb_payments.total), 2)) AS paid
FROM tb_payments
WHERE tb_payments.date='".date("Y-m-d")."'";
$result1=mysqli_query($db,$sql1);
$row1 = mysqli_fetch_assoc($result1);

$sql2="SELECT
CONCAT(FORMAT(SUM(tb_cart.price), 2)) AS unpaid
FROM tb_transactions
LEFT JOIN tb_cart ON tb_transactions.id=tb_cart.transaction_id
WHERE tb_transactions.status='unpaid' AND tb_transactions.date='".date("Y-m-d")."'";
$result2=mysqli_query($db,$sql2);
$row2 = mysqli_fetch_assoc($result2);

$currentsales=$row1['paid']+$row2['unpaid'];

$sql3="SELECT 
COUNT(tb_payments.id) AS paidcustomers
FROM tb_payments WHERE tb_payments.date='".date("Y-m-d")."'";
$result3=mysqli_query($db,$sql3);
$row3 = mysqli_fetch_assoc($result3);

$sql4="SELECT
COUNT(tb_transactions.id) as unpaidcustomers
FROM tb_transactions
WHERE tb_transactions.status='unpaid' AND tb_transactions.date='".date("Y-m-d")."'";
$result4=mysqli_query($db,$sql4);
$row4 = mysqli_fetch_assoc($result4);

$currentcustomers=$row3['paidcustomers']+$row4['unpaidcustomers'];

$sql5="SELECT DATE_FORMAT(tb_payments.date,'%M %d,%Y') AS date,
SUM(tb_cart.price*tb_cart.quantity) AS sales
FROM tb_payments 
JOIN tb_cart ON tb_payments.id=tb_cart.transaction_id
WHERE tb_payments.date NOT IN ('".date("Y-m-d")."')
GROUP BY tb_payments.date DESC
LIMIT 7";
$result5=mysqli_query($db,$sql5);

$sql6="SELECT
SUM(tb_cart.quantity) AS items
FROM tb_payments
JOIN tb_cart ON tb_payments.id=tb_cart.transaction_id
WHERE tb_payments.date='".date("Y-m-d")."'";
$result6=mysqli_query($db,$sql6);
$row6 = mysqli_fetch_assoc($result6);

$sql7="SELECT
CONCAT(FORMAT(SUM(tb_payments.total), 2)) AS paid
FROM tb_payments";
$result7=mysqli_query($db,$sql7);
$row7 = mysqli_fetch_assoc($result7);

$sql8="SELECT
CONCAT(FORMAT(SUM(tb_cart.total), 2)) AS receivable
FROM tb_transactions
JOIN tb_cart ON tb_transactions.id=tb_cart.transaction_id
WHERE tb_transactions.status='unpaid'";
$result8=mysqli_query($db,$sql8);
$row8 = mysqli_fetch_assoc($result8);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DASHBOARD</title>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
     google.charts.load("current", {packages:['corechart']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
      var data = google.visualization.arrayToDataTable([
        ["Element", "Sales", { role: "style" } ],
        <?php while($row5 = mysqli_fetch_array($result5)):;?>
          ['<?php echo $row5['date'];?>', <?php echo $row5['sales'];?>, 0],
          <?php endwhile; ?>
      ]);

      var view = new google.visualization.DataView(data);
      view.setColumns([0, 1,
                       { calc: "stringify",
                         sourceColumn: 1,
                         type: "string",
                         role: "annotation" },
                       2]);

                       var options = {
        title: "PREVIOUS SALES",
        width: 780,
        height: 370,
        bar: {groupWidth: "95%"},
        legend: { position: "none" },
      };
      var chart = new google.visualization.ColumnChart(document.getElementById("chart_div"));
      chart.draw(view, options);
          }
          
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link href="sidebars.css" rel="stylesheet">
</head>
  <body class="" onload="navbar();">
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
  <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
  <symbol id="bootstrap" viewBox="0 0 118 94">
    <title>Bootstrap</title>
    <path fill-rule="evenodd" clip-rule="evenodd" d="M24.509 0c-6.733 0-11.715 5.893-11.492 12.284.214 6.14-.064 14.092-2.066 20.577C8.943 39.365 5.547 43.485 0 44.014v5.972c5.547.529 8.943 4.649 10.951 11.153 2.002 6.485 2.28 14.437 2.066 20.577C12.794 88.106 17.776 94 24.51 94H93.5c6.733 0 11.714-5.893 11.491-12.284-.214-6.14.064-14.092 2.066-20.577 2.009-6.504 5.396-10.624 10.943-11.153v-5.972c-5.547-.529-8.934-4.649-10.943-11.153-2.002-6.484-2.28-14.437-2.066-20.577C105.214 5.894 100.233 0 93.5 0H24.508zM80 57.863C80 66.663 73.436 72 62.543 72H44a2 2 0 01-2-2V24a2 2 0 012-2h18.437c9.083 0 15.044 4.92 15.044 12.474 0 5.302-4.01 10.049-9.119 10.88v.277C75.317 46.394 80 51.21 80 57.863zM60.521 28.34H49.948v14.934h8.905c6.884 0 10.68-2.772 10.68-7.727 0-4.643-3.264-7.207-9.012-7.207zM49.948 49.2v16.458H60.91c7.167 0 10.964-2.876 10.964-8.281 0-5.406-3.903-8.178-11.425-8.178H49.948z"></path>
  </symbol>
  <symbol id="home" viewBox="0 0 16 16">
    <path d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5a.5.5 0 0 0 .5-.5v-4h2v4a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.354 1.146zM2.5 14V7.707l5.5-5.5 5.5 5.5V14H10v-4a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v4H2.5z"/>
  </symbol>
  <symbol id="speedometer2" viewBox="0 0 16 16">
    <path d="M8 4a.5.5 0 0 1 .5.5V6a.5.5 0 0 1-1 0V4.5A.5.5 0 0 1 8 4zM3.732 5.732a.5.5 0 0 1 .707 0l.915.914a.5.5 0 1 1-.708.708l-.914-.915a.5.5 0 0 1 0-.707zM2 10a.5.5 0 0 1 .5-.5h1.586a.5.5 0 0 1 0 1H2.5A.5.5 0 0 1 2 10zm9.5 0a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 0 1H12a.5.5 0 0 1-.5-.5zm.754-4.246a.389.389 0 0 0-.527-.02L7.547 9.31a.91.91 0 1 0 1.302 1.258l3.434-4.297a.389.389 0 0 0-.029-.518z"/>
    <path fill-rule="evenodd" d="M0 10a8 8 0 1 1 15.547 2.661c-.442 1.253-1.845 1.602-2.932 1.25C11.309 13.488 9.475 13 8 13c-1.474 0-3.31.488-4.615.911-1.087.352-2.49.003-2.932-1.25A7.988 7.988 0 0 1 0 10zm8-7a7 7 0 0 0-6.603 9.329c.203.575.923.876 1.68.63C4.397 12.533 6.358 12 8 12s3.604.532 4.923.96c.757.245 1.477-.056 1.68-.631A7 7 0 0 0 8 3z"/>
  </symbol>
  <symbol id="table" viewBox="0 0 16 16">
    <path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zm15 2h-4v3h4V4zm0 4h-4v3h4V8zm0 4h-4v3h3a1 1 0 0 0 1-1v-2zm-5 3v-3H6v3h4zm-5 0v-3H1v2a1 1 0 0 0 1 1h3zm-4-4h4V8H1v3zm0-4h4V4H1v3zm5-3v3h4V4H6zm4 4H6v3h4V8z"/>
  </symbol>
  <symbol id="people-circle" viewBox="0 0 16 16">
    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
    <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
  </symbol>
  <symbol id="grid" viewBox="0 0 16 16">
    <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z"/>
  </symbol>
</svg>
  <main class="d-flex" style="height: 800px;">
  <div id="navbar" class="d-flex flex-column flex-shrink-0 p-3 text-bg-secondary" style="width: 280px;">
      
  </div>

  <div class="b-example-divider b-example-vr p-2 flex-fill">
  <div id="content">
                <div class="container-fluid">

                    <div class="row">
                        <div class="col-md-6 col-xl-3 mb-4">
                            <div class="card shadow border-start-primary py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col me-2">
                                            <div class="text-uppercase text-primary text-xs mb-1"><span>SALES TODAY (<?php echo date("M d Y")?>)</span></div>
                                            <div class="text-dark fw-bold h5 mb-0"><span>P <?php echo $row1['paid'];?></span></div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-dollar-sign fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3 mb-4">
                            <div class="card shadow border-start-success py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col me-2">
                                            <div class="text-uppercase text-primary text-xs mb-1"><span>Customers</span></div>
                                            <div class="text-dark fw-bold h5 mb-0"><span></span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16">
                                              <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7Zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-5.784 6A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216ZM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/>
                                            </svg> <?php echo $row3['paidcustomers'];?></div>
                                        </div>
                                        <div class="col me-2">
                                            <div class="text-uppercase text-primary fw-bold text-xs mb-1"><span>ITEMS</span></div>
                                            <div class="text-dark fw-bold h5 mb-0"><span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-list-check" viewBox="0 0 16 16">
                                              <path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zM3.854 2.146a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708L2 3.293l1.146-1.147a.5.5 0 0 1 .708 0zm0 4a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708L2 7.293l1.146-1.147a.5.5 0 0 1 .708 0zm0 4a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0z"/>
                                            </svg> <?php echo $row6['items']; ?> PCS</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3 mb-4">
                            <div class="card shadow border-start-primary">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col me-2">
                                            <div class="text-uppercase text-success  text-xs mb-1"><span>ALL TIME SALES</span></div>
                                            <div class="text-dark fw-bold h5 mb-0"><span>P <?php echo $row7['paid']; ?></span></div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-dollar-sign fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3 mb-4">
                            <div class="card shadow border-start-warning py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col me-2">
                                            <div class="text-uppercase text-danger text-xs mb-1"><span>Receivables</span></div>
                                            <div class="text-dark fw-bold h5 mb-0"><span>P <?php echo $row8['receivable'];?></span></div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-comments fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-7 col-xl-8">
                            <div class="card shadow mb-4 " style="height: 400px;">
                                <div class="card-body">
                                    <div id="chart_div"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5 col-xl-4">
                            <div class="card shadow mb-4 px-3" style="height: 187px;">
                            <table style="width:100%;">
                
                <thead>
                <div class="text-uppercase text-muted">Recent Paid Transactions</div> 
                  <tr>
                    <th scope="col text-muted">Time</th>
                    <th scope="col text-muted">Items</th>
                    <th scope="col text-muted">Total</th>
                    <th scope="col text-muted">Change</th>
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
                      <td><?php echo $items['time']; ?></td>
                      <td><?php echo $items['items']; ?></td>
                      <td><?php echo $items['total']; ?></td>
                      <td><?php echo $items['change1']; ?></td>
                      </tr>
                <?php
                  } 
                } 
                  ?>
                </tbody>
              </table> 
                          </div>
                            <div class="card shadow mb-4 px-3" style="height: 187px;">
                            <table>
                
                <thead>
                <div class="text-uppercase text-muted">Recent Unpaid Transactions</div>
                  <tr>
                    <th scope="col">LastName</th>
                    <th scope="col">Time</th>
                    <th scope="col">Items</th>
                    <th scope="col">Total</th>
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
                      <td><?php echo $items['name']; ?></td>
                      <td><?php echo $items['time']; ?></td>
                      <td><?php echo $items['items']; ?></td>
                      <td><?php echo $items['total']; ?></td>
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
                    <div class="row">
                        <div class="col" style="height: 180px;">
                            <div class="card shadow mb-4" style="height: 165px;">
                                <div class="card-body" style="height: 165px;width: auto;">
                                <table style="width:100%;">
                                <thead>
                                <div class="text-uppercase text-muted">Zero Stocks</div>
                                  <tr>
                                    <th scope="col">Product ID</th>
                                    <th scope="col">Brand</th>
                                    <th scope="col">Specification</th>
                                    <th scope="col">Available</th>
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
                                      <td><?php echo $items['id']; ?></td>
                                      <td><?php echo $items['product_brand']; ?></td>
                                      <td><?php echo $items['specification']; ?></td>
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
                                            <div class="col" style="height: 180px;">
                                                <div class="card shadow mb-4 bg-danger-subtle" style="height: 165px;">
                                                    <div class="card-body" style="height: 165px;width: auto;">
                                                    <table style="width:100%;">
                                    
                                    <thead>
                                    <div class="text-uppercase text-muted">Low on Stocks</div>
                                      <tr>
                                        <th scope="col">Product ID</th>
                                        <th scope="col">Brand</th>
                                        <th scope="col">Specification</th>
                                        <th scope="col">Available</th>
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
                                          <td><?php echo $items['id']; ?></td>
                                          <td><?php echo $items['product_brand']; ?></td>
                                          <td><?php echo $items['specification']; ?></td>
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
                    </div>
                </div>
            </div>
  </div>

  </div>
</main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
  </body>
</html>