
<?php
include('user_session.php');
$sql1="SELECT
CONCAT(FORMAT(SUM(tb_payments.total), 2)) AS paid
FROM tb_payments";
$result1=mysqli_query($db,$sql1);
$row1 = mysqli_fetch_assoc($result1);

$sql2="SELECT
DATE_FORMAT(tb_payments.date,'%M %d,%Y') AS date1,
CONCAT(FORMAT(SUM(tb_cart.price*tb_cart.quantity), 2)) AS sales,
(SELECT COUNT(tb_payments.id)
FROM tb_payments WHERE tb_payments.date='".date("Y-m-d")."') AS paidcustomers,
SUM(tb_cart.quantity) AS paiditems
FROM tb_payments
JOIN tb_cart ON tb_payments.id=tb_cart.transaction_id
WHERE tb_payments.date='".date("Y-m-d")."'";
$result2=mysqli_query($db,$sql2);
$row2 = mysqli_fetch_assoc($result2);

$sql3="SELECT
COUNT(tb_transactions.id)AS transactions
FROM tb_transactions
WHERE tb_transactions.status='unpaid'";
$result3=mysqli_query($db,$sql3);
$row3 = mysqli_fetch_assoc($result3);

$sql4="SELECT
CONCAT(FORMAT(SUM(tb_cart.total), 2)) AS unpaid
FROM tb_transactions
JOIN tb_cart ON tb_transactions.id=tb_cart.transaction_id
WHERE tb_transactions.status='unpaid'";
$result4=mysqli_query($db,$sql4);
$row4 = mysqli_fetch_assoc($result4);

$sql5 = "SELECT 
CONCAT(FORMAT(SUM(tb_products.available*tb_products.price), 2)) AS amount
FROM tb_products";
$result5=mysqli_query($db,$sql5);
$row5 = mysqli_fetch_assoc($result5);

$sql6 = "SELECT
COUNT(tb_products.id)AS products
FROM tb_products
WHERE tb_products.available <= 5 AND tb_products.available
NOT IN (SELECT tb_products.available FROM tb_products WHERE tb_products.available='0')";
$result6=mysqli_query($db,$sql6);
$row6 = mysqli_fetch_assoc($result6);

$sql7 = "SELECT
COUNT(tb_products.id)AS products
FROM tb_products
WHERE tb_products.available='0'";
$result7=mysqli_query($db,$sql7);
$row7 = mysqli_fetch_assoc($result7);

$sql8="SELECT DATE_FORMAT(tb_payments.date,'%b %d') AS date,
SUM(tb_cart.price*tb_cart.quantity) AS sales
FROM tb_payments 
JOIN tb_cart ON tb_payments.id=tb_cart.transaction_id
WHERE tb_payments.date NOT IN ('".date("Y-m-d")."')
GROUP BY tb_payments.date DESC
LIMIT 7";
$result8=mysqli_query($db,$sql8);

$sql9="SELECT
date_format(tb_payments.date,'%b')AS month,
SUM(tb_cart.price*tb_cart.quantity)AS amount
FROM tb_payments
LEFT JOIN tb_cart ON tb_payments.id=tb_cart.transaction_id
GROUP BY year(tb_payments.date),month(tb_payments.date)
ORDER BY year(tb_payments.date),month(tb_payments.date) DESC
LIMIT 12";
$result9=mysqli_query($db,$sql9);

$sql10="SELECT
date_format(tb_payments.date,'%b')AS month,
SUM(tb_cart.price*tb_cart.quantity)AS amount
FROM tb_payments
LEFT JOIN tb_cart ON tb_payments.id=tb_cart.transaction_id
GROUP BY year(tb_payments.date),month(tb_payments.date)
ORDER BY year(tb_payments.date),month(tb_payments.date) DESC
LIMIT 12";
$result10=mysqli_query($db,$sql10);

if(!empty($_GET['selecteddate'])) {

  $dateS=date_create($_GET['selecteddate']);
  $TR = $_SESSION['id']."-".date_format($dateS,"Ymd")."-".date("His");
  header("location: admin_new_transaction.php?id=".$TR."&date=".date_format($dateS,"Y-m-d"));
}

$sql11="SELECT
SUM(tb_cart.quantity) AS items
FROM tb_cart";
$result11=mysqli_query($db,$sql11);
$row11 = mysqli_fetch_assoc($result11);

$sql12="SELECT
COUNT(tb_payments.id)AS transactions
FROM tb_payments";
$result12=mysqli_query($db,$sql12);
$row12 = mysqli_fetch_assoc($result12);

$sql13="SELECT
SUM(tb_cart.quantity) AS items
FROM tb_transactions
JOIN tb_cart ON tb_transactions.id=tb_cart.transaction_id
WHERE tb_transactions.status='unpaid';";
$result13=mysqli_query($db,$sql13);
$row13 = mysqli_fetch_assoc($result13);

$sql14="SELECT 
FORMAT(AVG(tb_payments.total), 2) AS daily
FROM tb_payments";
$result14=mysqli_query($db,$sql14);
$row14 = mysqli_fetch_assoc($result14);

$sql15="SELECT
FORMAT(SUM(tb_payments.total) / 12, 2) AS monthly
FROM tb_payments
WHERE YEAR(tb_payments.date) = year(curdate())";
$result15=mysqli_query($db,$sql15);
$row15 = mysqli_fetch_assoc($result15);

$sql16="SELECT
tb_products.category,
SUM(tb_cart.quantity) AS pcs
FROM tb_cart
JOIN tb_products ON tb_cart.product_id=tb_products.id
GROUP BY tb_products.category
ORDER BY pcs DESC
LIMIT 5";
$result16=mysqli_query($db,$sql16);

$sql17="SELECT
tb_products.category,
SUM(tb_cart.quantity) AS pcs
FROM tb_cart
JOIN tb_products ON tb_cart.product_id=tb_products.id
GROUP BY tb_products.category
ORDER BY pcs DESC
LIMIT 5";
$result17=mysqli_query($db,$sql17);

$sql18 = "SELECT 
CONCAT(FORMAT(SUM(tb_products.stocks*tb_products.price), 2)) AS amount
FROM tb_products";
$result18=mysqli_query($db,$sql18);
$row18 = mysqli_fetch_assoc($result18);

$sql19 = "SELECT
FORMAT(SUM(tb_products.capital*tb_cart.quantity) , 2) AS capital,
FORMAT(SUM(tb_cart.price*tb_cart.quantity - tb_products.capital*tb_cart.quantity) , 2) AS profit
FROM tb_transactions
INNER JOIN tb_cart ON tb_transactions.id=tb_cart.transaction_id
LEFT JOIN tb_products ON tb_cart.product_id=tb_products.id
WHERE tb_transactions.status='paid'";
$result19=mysqli_query($db,$sql19);
$row19 = mysqli_fetch_assoc($result19);

?>
<!doctype html>
<html lang="en" data-bs-theme="auto">
  <head><script src="../assets/js/color-modes.js"></script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.111.3">
    <title>DASHBOARD</title>
 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
      body {
  font-size: .875rem;
}

.feather {
  width: 16px;
  height: 16px;
}

/*
 * Sidebar
 */

.sidebar {
  position: fixed;
  top: 0;
  /* rtl:raw:
  right: 0;
  */
  bottom: 0;
  /* rtl:remove */
  left: 0;
  z-index: 100; /* Behind the navbar */
  padding: 48px 0 0; /* Height of navbar */
  box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
}


.sidebar-sticky {
  height: calc(100vh - 48px);
  overflow-x: hidden;
  overflow-y: auto; /* Scrollable contents if viewport is shorter than content. */
}

.sidebar .nav-link {
  font-weight: 500;
  color: #333;
}

.sidebar .nav-link .feather {
  margin-right: 4px;
  color: #727272;
}

.sidebar .nav-link.active {
  color: #2470dc;
}

.sidebar .nav-link:hover .feather,
.sidebar .nav-link.active .feather {
  color: inherit;
}

.sidebar-heading {
  font-size: .75rem;
}

/*
 * Navbar
 */

.navbar-brand {
  padding-top: .75rem;
  padding-bottom: .75rem;
  background-color: rgba(0, 0, 0, .25);
  box-shadow: inset -1px 0 0 rgba(0, 0, 0, .25);
}

.navbar .navbar-toggler {
  top: .25rem;
  right: 1rem;
}

.navbar .form-control {
  padding: .75rem 1rem;
}

.form-control-dark {
  color: #fff;
  background-color: rgba(255, 255, 255, .1);
  border-color: rgba(255, 255, 255, .1);
}

.form-control-dark:focus {
  border-color: transparent;
  box-shadow: 0 0 0 3px rgba(255, 255, 255, .25);
}

  </style>

    
  </head>
  <body>


    <div class="dropdown position-fixed bottom-0 end-0 mb-3 me-3">
      <button class="btn btn-bd-primary d-flex align-items-center"
              type="button"
              data-bs-toggle="dropdown">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
              </svg>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bd-theme-text">
        <li>
          <button type="button" class="dropdown-item d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#exampleModal" data-bs-theme-value="light" aria-pressed="false">
           New Transaction
          </button>
                  
        </li>
      </ul>
    </div>
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                          <div class="modal-header">
                          <h6 class="modal-title" id="exampleModalLabel">CREATE TRANSACTION</h6>
                          </div>
                          <div class="modal-body">
                            <form method="get" enctype="multipart/form-data">
                              <div class="mb-3">
                                
                              <input id="startDate" type="date" name="selecteddate" class="form-control" id="validationDefault01" required>
                                
                              </div>
                            
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-danger">Create</button>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
  
  

    
<header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
  <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="#">R-Click POS: Karaang Garahe</a>
  <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
 
</header>

<div class="container-fluid">
  <div class="row">
    <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-body-tertiary sidebar collapse">
      <div class="position-sticky pt-3 sidebar-sticky">
      <ul class="nav flex-column">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="admin_dashboard.php">
              <span data-feather="home" class="align-text-bottom"></span>
              Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="admin_yearly_history.php">
              <span data-feather="file" class="align-text-bottom"></span>
              Paid Transactions
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="admin_unpaid_transactions.php">
              <span data-feather="file" class="align-text-bottom"></span>
              Unpaid Transactions
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="admin_products.php">
              <span data-feather="shopping-cart" class="align-text-bottom"></span>
              Products
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="admin_inventory.php">
              <span data-feather="bar-chart-2" class="align-text-bottom"></span>
              Inventory
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="admin_users.php">
              <span data-feather="users" class="align-text-bottom"></span>
              Users
            </a>
          </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-body-secondary text-uppercase">
          <span>Quick Links</span>
        </h6>
        <ul class="nav flex-column mb-5">
          <li class="nav-item">
            <a class="nav-link" href="admin_generateqr.php">
              <span data-feather="file-text" class="align-text-bottom"></span>
              QR Generator
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="admin_add_products.php">
              <span data-feather="file-text" class="align-text-bottom"></span>
              Add new product
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="admin_product_restock.php?id=20230419234321">
              <span data-feather="file-text" class="align-text-bottom"></span>
              Re-stock product
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="admin_add_category.php">
              <span data-feather="file-text" class="align-text-bottom"></span>
              Add new category
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="admin_add_supplier.php">
              <span data-feather="file-text" class="align-text-bottom"></span>
              Add new supplier
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="admin_add_mc.php">
              <span data-feather="file-text" class="align-text-bottom"></span>
              Add new mc brand & model
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="admin_add_user.php">
              <span data-feather="file-text" class="align-text-bottom"></span>
              Add new user
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="admin_add_discount.php">
              <span data-feather="file-text" class="align-text-bottom"></span>
              Add new discounts
            </a>
          </li>
        </ul>
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-body-secondary text-uppercase">
          <span>Account</span>
        </h6>
        <ul class="nav flex-column">
          <li class="nav-item">
            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
              <span data-feather="user" class="align-text-bottom"></span>
              <strong><?php echo $name; ?></strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                <li><a class="dropdown-item" href="admin_settings.php">Settings</a></li>
                <li><a class="dropdown-item" href="signout.php">Sign out</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </nav>

    <main class="col-md-9 ms-sm-auto col-lg-10">

      <div class="pt-3">
        <div class="row">
          
          <div class="col border rounded shadow m-1">
            <div class="card-body p-2">
              <div class="float-end">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-currency-dollar" viewBox="0 0 16 16">
                  <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z"/>
                </svg>
              </div>
                <h6 class="text-muted fw-normal mt-0" title="Number of Customers">Revenue</h6>
                <h3 class=""><?php echo $row1['paid'];?></h3>
                <p class="mb-0 text-muted">
                <span class="text-primary fw-bold"><?php echo $row12['transactions'];?></span>
                <span class="text-nowrap me-2">Receipts</span>
                <span class="text-primary fw-bold"><?php echo $row11['items'];?></span>
                <span class="text-nowrap me-2">Products Sold</span>
                </p>
            </div>
          </div>

          <div class="col border rounded shadow m-1">
            <div class="card-body p-2">
              <div class="float-end">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-currency-dollar" viewBox="0 0 16 16">
                  <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z"/>
                </svg>
              </div>
                <h6 class="text-muted fw-normal mt-0" title="Number of Customers">Net Profit</h6>
                <h3 class=""><?php echo $row19['profit'];?></h3>
                <p class="mb-0 text-muted">
                <span class="text-primary fw-bold"><?php echo $row19['capital'];?></span>
                <span class="text-nowrap me-1">Capital</span>
  
                </p>
            </div>
          </div>

          <div class="col border rounded shadow m-1">
            <div class="card-body p-2">
              <div class="float-end">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-currency-dollar" viewBox="0 0 16 16">
                  <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z"/>
                </svg>
              </div>
                <h6 class="text-muted fw-normal mt-0" title="Number of Customers">Receivable</h6>
                <h3 class=""><?php echo $row4['unpaid'];?></h3>
                <p class="mb-0 text-muted">
                <span class="text-primary fw-bold"><?php echo $row3['transactions'];?></span>
                <span class="text-nowrap me-2">Accounts</span>
                <span class="text-primary fw-bold"><?php echo $row13['items'];?></span>
                <span class="text-nowrap me-2">Products</span>
                </p>
            </div>
          </div>
          
        </div>
      </div>
      <div class="pt-3">
        <div class="row">
          
          <div class="col border rounded shadow m-1">
            <div class="card-body p-2">
              <div class="float-end">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-list-ol" viewBox="0 0 16 16">
                  <path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5"/>
                  <path d="M1.713 11.865v-.474H2c.217 0 .363-.137.363-.317 0-.185-.158-.31-.361-.31-.223 0-.367.152-.373.31h-.59c.016-.467.373-.787.986-.787.588-.002.954.291.957.703a.595.595 0 0 1-.492.594v.033a.615.615 0 0 1 .569.631c.003.533-.502.8-1.051.8-.656 0-1-.37-1.008-.794h.582c.008.178.186.306.422.309.254 0 .424-.145.422-.35-.002-.195-.155-.348-.414-.348h-.3zm-.004-4.699h-.604v-.035c0-.408.295-.844.958-.844.583 0 .96.326.96.756 0 .389-.257.617-.476.848l-.537.572v.03h1.054V9H1.143v-.395l.957-.99c.138-.142.293-.304.293-.508 0-.18-.147-.32-.342-.32a.33.33 0 0 0-.342.338zM2.564 5h-.635V2.924h-.031l-.598.42v-.567l.629-.443h.635z"/>
                </svg>
              </div>
                <h6 class="text-muted fw-normal mt-0" title="Number of Customers">Inventory</h6>
                <h3 class=""><?php echo $row5['amount']; ?></h3>
                <p class="mb-0 text-muted">
                <span class="text-primary fw-bold"><?php echo $row18['amount']; ?></span>
                <span class="text-nowrap me-2">Total</span>
                <span class="text-primary fw-bold"><?php echo $row6['products']; ?></span>
                <span class="text-nowrap me-2">Low Stocks</span> 
                <span class="text-primary fw-bold"><?php echo $row7['products']; ?></span>
                <span class="text-nowrap me-2">Zero Stocks</span>
                </p>
            </div>
          </div>

          <div class="col border rounded shadow m-1">
            <div class="card-body p-2">
              <div class="float-end">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-list-ol" viewBox="0 0 16 16">
                  <path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5"/>
                  <path d="M1.713 11.865v-.474H2c.217 0 .363-.137.363-.317 0-.185-.158-.31-.361-.31-.223 0-.367.152-.373.31h-.59c.016-.467.373-.787.986-.787.588-.002.954.291.957.703a.595.595 0 0 1-.492.594v.033a.615.615 0 0 1 .569.631c.003.533-.502.8-1.051.8-.656 0-1-.37-1.008-.794h.582c.008.178.186.306.422.309.254 0 .424-.145.422-.35-.002-.195-.155-.348-.414-.348h-.3zm-.004-4.699h-.604v-.035c0-.408.295-.844.958-.844.583 0 .96.326.96.756 0 .389-.257.617-.476.848l-.537.572v.03h1.054V9H1.143v-.395l.957-.99c.138-.142.293-.304.293-.508 0-.18-.147-.32-.342-.32a.33.33 0 0 0-.342.338zM2.564 5h-.635V2.924h-.031l-.598.42v-.567l.629-.443h.635z"/>
                </svg>
              </div>
                <h6 class="text-muted fw-normal mt-0" title="Number of Customers">Monthly Avg</h6>
                <h3 class=""><?php echo $row15['monthly'];?></h3>
                <p class="mb-0 text-muted">
                <span class="text-primary fw-bold"><?php echo $row14['daily'];?></span>
                <span class="text-nowrap me-2">Daily</span>
                </p>
            </div>
          </div>

          <div class="col border rounded shadow m-1">
            <div class="card-body p-2">
              <div class="float-end">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-list-ol" viewBox="0 0 16 16">
                  <path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5"/>
                  <path d="M1.713 11.865v-.474H2c.217 0 .363-.137.363-.317 0-.185-.158-.31-.361-.31-.223 0-.367.152-.373.31h-.59c.016-.467.373-.787.986-.787.588-.002.954.291.957.703a.595.595 0 0 1-.492.594v.033a.615.615 0 0 1 .569.631c.003.533-.502.8-1.051.8-.656 0-1-.37-1.008-.794h.582c.008.178.186.306.422.309.254 0 .424-.145.422-.35-.002-.195-.155-.348-.414-.348h-.3zm-.004-4.699h-.604v-.035c0-.408.295-.844.958-.844.583 0 .96.326.96.756 0 .389-.257.617-.476.848l-.537.572v.03h1.054V9H1.143v-.395l.957-.99c.138-.142.293-.304.293-.508 0-.18-.147-.32-.342-.32a.33.33 0 0 0-.342.338zM2.564 5h-.635V2.924h-.031l-.598.42v-.567l.629-.443h.635z"/>
                </svg>
              </div>
                <h6 class="text-muted fw-normal mt-0" title="Number of Customers">Today <?php echo date("M j,Y");?></h6>
                <h3 class=""><?php echo $row2['sales'];?></h3>
                <p class="mb-0 text-muted">
                <span class="text-primary fw-bold"><?php echo $row2['paidcustomers'];?></span>
                <span class="text-nowrap me-2">Receipts</span>
                <span class="text-primary fw-bold"><?php echo $row2['paiditems'];?></span>
                <span class="text-nowrap me-2">Products Sold</span> 
                </p>
            </div>
          </div>
          
        </div>
      </div>

      <div class="row mt-3">
        <div class="col-md">
          <h6 class="mt-2">Monthly Sales Chart</h6>
          <canvas class="my-4 w-100" id="myChart" width="200" height="120"></canvas>
        </div>
        <div class="col-md">
          <h6 class="mt-2">Top 5 Selling Categories</h6>
          <canvas class="my-4 w-100" id="myChart2" width="200" height="120"></canvas>
        </div>
      </div>
      
      <h6>Recent Paid Transactions</h6>
      <div class="table-responsive">
        <table class="table table-hover table-sm">
          <thead>
            <tr>
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
            <tr onclick="location.href='admin_transaction.php?id=<?php echo $items['id'];?>'">  
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
    </main>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/feather-icons@4.28.0/dist/feather.min.js" integrity="sha384-uO3SXW5IuS1ZpFPKugNNWqTZRRglnUJK6UAZ/gxOX80nxEkN9NcGZTftn6RzhGWE" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</script>
<script>
  /* globals Chart:false, feather:false */

(() => {
  'use strict'

  feather.replace({ 'aria-hidden': 'true' })

  // Graphs
  const ctx = document.getElementById('myChart')
  // eslint-disable-next-line no-unused-vars
  const myChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: [
        <?php while($row10 = mysqli_fetch_array($result10)):;?>
          ['<?php echo $row10['month'];?>'],
          <?php endwhile; ?>
      ],
      datasets: [{
        data: [
          <?php while($row9 = mysqli_fetch_array($result9)):;?>
          <?php echo $row9['amount'];?>,
          <?php endwhile; ?>
        ],
        lineTension: 0,
        backgroundColor: '#610C9F'
      }]
    },
    options: {
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          boxPadding: 3
        }
      }
    }
  })
}

)()

</script>
<script>
  /* globals Chart:false, feather:false */

(() => {
  'use strict'

  feather.replace({ 'aria-hidden': 'true' })

  // Graphs
  const ctx = document.getElementById('myChart2')
  // eslint-disable-next-line no-unused-vars
  const myChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: [
        <?php while($row16 = mysqli_fetch_array($result16)):;?>
          ['<?php echo $row16['category'];?>'],
          <?php endwhile; ?>
  ],
      datasets:  [{
    label: 'My First Dataset',
    data: [<?php while($row17 = mysqli_fetch_array($result17)):;?>
          <?php echo $row17['pcs'];?>,
          <?php endwhile; ?>],
    backgroundColor: [
      '#610C9F','#DA0C81','#F05941','#FFB000','#279EFF'
    ],
    hoverOffset: 4
  }]
    },
    options: {
      aspectRatio: 2,
      plugins: {
        legend: {
          display: true
        }
        
      }
    }
  })
}
)()

</script>

  </body>
</html>
