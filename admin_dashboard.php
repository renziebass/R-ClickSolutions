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
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Dashboard - Brand</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="assets/fonts/fontawesome5-overrides.min.css">
    <link rel="stylesheet" href="assets/css/Navigation-Clean.css">
    <link rel="stylesheet" href="assets/css/Navigation-with-Search.css">
</head>

<body id="page-top">
    <div id="wrapper">
        <nav class="navbar navbar-dark align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0" style="color: var(--bs-pink);background: var(--bs-gray-dark);">
            <div class="container-fluid d-flex flex-column p-0"><a class="navbar-brand d-flex justify-content-center align-items-center sidebar-brand m-0" href="#">
                    <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-laugh-wink"></i></div>
                    <div class="sidebar-brand-text mx-3"><span>KG SHOP POs</span></div>
                </a>
                <hr class="sidebar-divider my-0">
                <ul class="navbar-nav text-light" id="accordionSidebar">
                    <li class="nav-item"><a class="nav-link active" href="index.html"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="transactions.html"><i class="fas fa-user"></i><span>Transactions</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="table.html"><i class="fas fa-table"></i><span>Products</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="login.html"><i class="far fa-user-circle"></i><span>Suppliers</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="login.html"><i class="far fa-user-circle"></i><span>Logout</span></a></li>
                    <li class="nav-item"></li>
                </ul><span class="text-center d-flex d-lg-flex" style="font-size: 12px;margin: 0px;margin-top: 0px;margin-bottom: 0px;padding: 0px;padding-bottom: 0px;padding-top: 0px;">Copyright © Brand 2023</span>
                <div class="text-center d-none d-md-inline"></div>
            </div>
        </nav>
        <div class="d-flex flex-column" id="content-wrapper">
            <div id="content">
                <div class="container-fluid">
                    <div class="d-sm-flex justify-content-between align-items-center mb-4"></div>
                    <div class="row">
                        <div class="col-md-6 col-xl-3 mb-4">
                            <div class="card shadow border-start-primary py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col me-2">
                                            <div class="text-uppercase text-primary fw-bold text-xs mb-1"><span>SALES TODAY (July 24,2023)</span></div>
                                            <div class="text-dark fw-bold h5 mb-0"><span>$40,000</span></div>
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
                                            <div class="text-uppercase text-success fw-bold text-xs mb-1"><span>Customers</span></div>
                                            <div class="text-dark fw-bold h5 mb-0"><span>5</span></div>
                                        </div>
                                        <div class="col me-2">
                                            <div class="text-uppercase text-success fw-bold text-xs mb-1"><span>ITEMS</span></div>
                                            <div class="text-dark fw-bold h5 mb-0"><span>8</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3 mb-4">
                            <div class="card shadow border-start-primary py-2">
                                <div class="card-body">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col me-2">
                                            <div class="text-uppercase text-primary fw-bold text-xs mb-1"><span>SALES TODAY (July 24,2023)</span></div>
                                            <div class="text-dark fw-bold h5 mb-0"><span>$40,000</span></div>
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
                                            <div class="text-uppercase text-warning fw-bold text-xs mb-1"><span>Pending Requests</span></div>
                                            <div class="text-dark fw-bold h5 mb-0"><span>18</span></div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-comments fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-7 col-xl-8">
                            <div class="card shadow mb-4" style="height: 400px;">
                                <div class="card-body" style="height: 280px;"></div>
                            </div>
                        </div>
                        <div class="col-lg-5 col-xl-4">
                            <div class="card shadow mb-4" style="height: 187px;"></div>
                            <div class="card shadow mb-4" style="height: 187px;"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-5 col-xl-4" style="height: 180px;width: 600px;">
                            <div class="card shadow mb-4" style="height: 165px;">
                                <div class="card-body" style="height: 165px;width: auto;">
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
                            </div>
                        </div>
                        <div class="col-lg-5 col-xl-4" style="height: 180px;width: 600px;">
                            <div class="card shadow mb-4" style="height: 165px;">
                                <div class="card-body" style="height: 165px;width: auto;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><a class="border rounded d-inline scroll-to-top" href="#page-top"><i class="fas fa-angle-up"></i></a>
    </div>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/theme.js"></script>
</body>

</html>