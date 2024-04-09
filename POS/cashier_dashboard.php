
<?php
include('user_session.php');

$isMob = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "mobile")); 
if($isMob){ 
  $newTR_loc = "cashier_new_transaction";
}else{ 
  $newTR_loc = "cashier_new_transaction2";
}

$sql2="SELECT
DATE_FORMAT(tb_payments.date,'%M %d,%Y') AS date1,
FORMAT(SUM(tb_payments.total),2)AS sales,
COUNT(tb_payments.id) AS paidcustomers
FROM tb_payments
WHERE tb_payments.date='".date("Y-m-d")."'";
$result2=mysqli_query($db,$sql2);
$row2 = mysqli_fetch_assoc($result2);

$sql2b = "SELECT
SUM(tb_cart.quantity) AS paiditems
FROM tb_cart
WHERE tb_cart.date='".date("Y-m-d")."'";
$result2b=mysqli_query($db,$sql2b);
$row2b = mysqli_fetch_assoc($result2b);

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


$sql13="SELECT
SUM(tb_cart.quantity) AS items
FROM tb_transactions
JOIN tb_cart ON tb_transactions.id=tb_cart.transaction_id
WHERE tb_transactions.status='unpaid';";
$result13=mysqli_query($db,$sql13);
$row13 = mysqli_fetch_assoc($result13);

  //$TR = $_SESSION['id']."-".date("Ymd")."-".date("His");

$sql14 = "SELECT
COUNT(tb_payments.id) AS cashcustomers,
SUM(tb_payments.total) AS cashpayments
FROM tb_payments
WHERE tb_payments.payment1='CASH' AND
tb_payments.date='".date("Y-m-d")."'";
$result14=mysqli_query($db,$sql14);
$row14 = mysqli_fetch_assoc($result14);

$sql15 = "SELECT
COUNT(tb_payments.id) AS othercustomers,
SUM(tb_payments.total) AS otherpayments
FROM tb_payments
WHERE tb_payments.payment1!='CASH' AND
tb_payments.date='".date("Y-m-d")."'";
$result15=mysqli_query($db,$sql15);
$row15 = mysqli_fetch_assoc($result15);
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
      <div class="btn-group-vertical">
        <button type="button" class="btn btn-success btn-sm" style="--bs-btn-padding-y: .15rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;" accesskey="c" onclick="location.href='<?php echo $newTR_loc;?>.php?date=<?php echo date('Y-m-d')?>'" >ALT+C</button>
        <button type="button" class="btn btn-sm btn-outline-success fw-bold bg-white" accesskey="c" onclick="location.href='<?php echo $newTR_loc;?>.php?date=<?php echo date('Y-m-d')?>'" >NEW TR</button>
      </div>
      <div class="btn-group-vertical">
        <button type="button" class="btn btn-warning btn-sm" style="--bs-btn-padding-y: .15rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;" accesskey="v" onclick="location.href='cashier_new_transaction3.php?date=<?php echo date('Y-m-d')?>'" >ALT+V</button>
        <button type="button" class="btn btn-sm btn-outline-warning fw-bold bg-white" accesskey="v" onclick="location.href='cashier_new_transaction3.php?date=<?php echo date('Y-m-d')?>'" >WS TR</button>
      </div>
    </div>
    
 
  
  

    
<header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
  <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="#">R-Click POS</a>
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
            <a class="nav-link active" aria-current="page" href="cashier_dashboard.php">
              <span data-feather="home" class="align-text-bottom"></span>
              Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="cashier_transactions_history.php">
              <span data-feather="file" class="align-text-bottom"></span>
              Paid Transactions
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="cashier_unpaid_transactions.php">
              <span data-feather="file" class="align-text-bottom"></span>
              Unpaid Transactions
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="cashier_products.php">
              <span data-feather="shopping-cart" class="align-text-bottom"></span>
              Products
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
                <li><a class="dropdown-item" href="cashier_settings.php">Settings</a></li>
                <li><a class="dropdown-item" href="signout.php">Sign out</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </nav>

    <main class="col-md-9 ms-sm-auto col-lg-10 bg-light">
    <div class="">
    <h6 class="text-center m-2"><?php echo date("F j,Y");?></h6>
        <div class="row">

          <div class="col text-start p-2 ">
            <div class="card-body p-2 border-start border-4 shadow rounded border-success bg-white">
            <div class="row">
              <p class="col m-0 text-success">
                SALES
              </p>
              <p class="col m-0 d-flex justify-content-end text-success">
              <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" fill="currentColor" class="bi bi-cash-coin" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M11 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8m5-4a5 5 0 1 1-10 0 5 5 0 0 1 10 0"/>
                <path d="M9.438 11.944c.047.596.518 1.06 1.363 1.116v.44h.375v-.443c.875-.061 1.386-.529 1.386-1.207 0-.618-.39-.936-1.09-1.1l-.296-.07v-1.2c.376.043.614.248.671.532h.658c-.047-.575-.54-1.024-1.329-1.073V8.5h-.375v.45c-.747.073-1.255.522-1.255 1.158 0 .562.378.92 1.007 1.066l.248.061v1.272c-.384-.058-.639-.27-.696-.563h-.668zm1.36-1.354c-.369-.085-.569-.26-.569-.522 0-.294.216-.514.572-.578v1.1zm.432.746c.449.104.655.272.655.569 0 .339-.257.571-.709.614v-1.195z"/>
                <path d="M1 0a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h4.083q.088-.517.258-1H3a2 2 0 0 0-2-2V3a2 2 0 0 0 2-2h10a2 2 0 0 0 2 2v3.528c.38.34.717.728 1 1.154V1a1 1 0 0 0-1-1z"/>
                <path d="M9.998 5.083 10 5a2 2 0 1 0-3.132 1.65 6 6 0 0 1 3.13-1.567"/>
              </svg>
              </p>
            </div>
          
                <h4 class="text-success"><span>&#8369;<?php
                  $sales=null;
                  if (empty($row2['sales'])) {
                    $sales = 0;
                  } else {
                    $sales = $row2['sales'];
                  }
                  echo $sales;
                  ?></span>
                </h4>
                <p class="mb-0 text-muted">
                <span class="text-success fw-bold"><?php echo $row2['paidcustomers'];?></span>
                <span class="text-nowrap me-2">Receipts</span>
                <br>
                <span class="text-success fw-bold">
                  <?php
                  $product_sold=null;
                  if (empty($row2b['paiditems'])) {
                    $product_sold = 0;
                  } else {
                    $product_sold = $row2b['paiditems'];
                  }
                  echo $product_sold;
                  ?>
                </span>
                <span class="text-nowrap">Products</span> 
                </p>
            </div>
          </div>

          <div class="col text-start p-2">
            <div class="card-body p-2 border-start border-4 shadow rounded border-primary bg-white">
            <div class="row">
              <p class="col m-0 text-primary">
                CASH
              </p>
              <p class="col m-0 d-flex justify-content-end text-primary">
              <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" fill="currentColor" class="bi bi-cash-coin" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M11 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8m5-4a5 5 0 1 1-10 0 5 5 0 0 1 10 0"/>
                <path d="M9.438 11.944c.047.596.518 1.06 1.363 1.116v.44h.375v-.443c.875-.061 1.386-.529 1.386-1.207 0-.618-.39-.936-1.09-1.1l-.296-.07v-1.2c.376.043.614.248.671.532h.658c-.047-.575-.54-1.024-1.329-1.073V8.5h-.375v.45c-.747.073-1.255.522-1.255 1.158 0 .562.378.92 1.007 1.066l.248.061v1.272c-.384-.058-.639-.27-.696-.563h-.668zm1.36-1.354c-.369-.085-.569-.26-.569-.522 0-.294.216-.514.572-.578v1.1zm.432.746c.449.104.655.272.655.569 0 .339-.257.571-.709.614v-1.195z"/>
                <path d="M1 0a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h4.083q.088-.517.258-1H3a2 2 0 0 0-2-2V3a2 2 0 0 0 2-2h10a2 2 0 0 0 2 2v3.528c.38.34.717.728 1 1.154V1a1 1 0 0 0-1-1z"/>
                <path d="M9.998 5.083 10 5a2 2 0 1 0-3.132 1.65 6 6 0 0 1 3.13-1.567"/>
              </svg>
              </p>
            </div>
                <h4 class="text-primary"><span>&#8369;<?php
                  $cashpayments=null;
                  if (empty($row14['cashpayments'])) {
                    $cashpayments = 0;
                  } else {
                    $cashpayments = $row14['cashpayments'];
                  }
                  echo $cashpayments;
                  ?></span>
                </h4>
                <p class="mb-0 text-muted">
                  <br>
                <span class="text-primary fw-bold"><?php echo $row14['cashcustomers'] ?></span>
                <span class="text-nowrap me-2">Payments</span>
                </p>
            </div>
          </div>

          <div class="col text-start p-2">
            <div class="card-body p-2 border-start border-4 shadow rounded border-warning bg-white">
            <div class="row">
              <p class="col m-0 text-warning">
                OTHER
              </p>
              <p class="col m-0 d-flex justify-content-end text-warning">
              <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" fill="currentColor" class="bi bi-bank" viewBox="0 0 16 16">
                <path d="m8 0 6.61 3h.89a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5H15v7a.5.5 0 0 1 .485.38l.5 2a.498.498 0 0 1-.485.62H.5a.498.498 0 0 1-.485-.62l.5-2A.5.5 0 0 1 1 13V6H.5a.5.5 0 0 1-.5-.5v-2A.5.5 0 0 1 .5 3h.89zM3.777 3h8.447L8 1zM2 6v7h1V6zm2 0v7h2.5V6zm3.5 0v7h1V6zm2 0v7H12V6zM13 6v7h1V6zm2-1V4H1v1zm-.39 9H1.39l-.25 1h13.72z"/>
              </svg>
              </p>
            </div>
                <h4 class="text-warning"><span>&#8369;<?php
                  $otherpayments=null;
                  if (empty($row15['otherpayments'])) {
                    $otherpayments = 0;
                  } else {
                    $otherpayments = $row15['otherpayments'];
                  }
                  echo $otherpayments;
                  ?></span>
                </h4>
                <p class="mb-0 text-muted">
                  <br>
                <span class="text-warning fw-bold"><?php echo $row15['othercustomers'] ?></span>
                <span class="text-nowrap me-2">Payments</span>
                </p>
            </div>
          </div>

          <div class="col text-start p-2">
            <div class="card-body p-2 border-start border-4 shadow rounded border-danger bg-white">
                <div class="row">
                  <p class="col m-0 text-danger">
                    RECEIVABLE
                  </p>
                  <p class="col m-0 d-flex justify-content-end text-danger">
                  <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" fill="currentColor" class="bi bi-person-lines-fill" viewBox="0 0 16 16">
                    <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zM11 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5m.5 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1zm2 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1zm0 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1z"/>
                  </svg>
                  </p>
                </div>
                <h4 class="text-danger"><span>&#8369;<?php
                  $unpaid=null;
                  if (empty($row4['unpaid'])) {
                    $unpaid = 0;
                  } else {
                    $unpaid = $row4['unpaid'];
                  }
                  echo $unpaid;
                  ?></span>
                </h4>
                <p class="mb-0 text-muted">
                <span class="text-danger fw-bold"><?php echo $row3['transactions'];?></span>
                <span class="text-nowrap me-2">Accounts</span>
                <br>
                <span class="text-danger fw-bold">
                <?php
                  $items=null;
                  if (empty($row13['items'])) {
                    $items = 0;
                  } else {
                    $items = $row13['items'];
                  }
                  echo $items;
                  ?>
                </span>
                <span class="text-nowrap">Items</span> 
                </p>
            </div>
          </div>
          
        </div>
        <div class="row">

        <div class="col text-start p-2">
            <div class="card-body p-2 border-top border-3 border-success shadow rounded bg-white">
                <div class="row mb-3">
                  <p class="col-8 m-0">
                    RECENT PAID TRANSACTIONS
                  </p>
                  <p class="col m-0 d-flex justify-content-end text-success">
                  <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" fill="currentColor" class="bi bi-list-ol" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5"/>
                    <path d="M1.713 11.865v-.474H2c.217 0 .363-.137.363-.317 0-.185-.158-.31-.361-.31-.223 0-.367.152-.373.31h-.59c.016-.467.373-.787.986-.787.588-.002.954.291.957.703a.595.595 0 0 1-.492.594v.033a.615.615 0 0 1 .569.631c.003.533-.502.8-1.051.8-.656 0-1-.37-1.008-.794h.582c.008.178.186.306.422.309.254 0 .424-.145.422-.35-.002-.195-.155-.348-.414-.348h-.3zm-.004-4.699h-.604v-.035c0-.408.295-.844.958-.844.583 0 .96.326.96.756 0 .389-.257.617-.476.848l-.537.572v.03h1.054V9H1.143v-.395l.957-.99c.138-.142.293-.304.293-.508 0-.18-.147-.32-.342-.32a.33.33 0 0 0-.342.338zM2.564 5h-.635V2.924h-.031l-.598.42v-.567l.629-.443h.635z"/>
                  </svg>
                  </p>
                </div>

        <table class="table table-hover table-sm mb-5" style="margin-bottom: 70px;">
          <thead>
            <tr class="text-muted">
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
                        SUM(tb_cart.quantity) as items,
                        CONCAT(FORMAT(SUM(tb_cart.price*tb_cart.quantity), 2)) AS total2,
                        tb_payments.total,
                        CONCAT(tb_payments.payment1,' - ',FORMAT(tb_payments.payment, 2)) AS payment,
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
            <tr onclick="location.href='cashier_transaction.php?id=<?php echo $items['id'];?>'">  
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
          </div>

        </div>
      </div>
      


    </main>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/feather-icons@4.28.0/dist/feather.min.js" integrity="sha384-uO3SXW5IuS1ZpFPKugNNWqTZRRglnUJK6UAZ/gxOX80nxEkN9NcGZTftn6RzhGWE" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</script><script src="dashboard.js"></script>
  </body>
</html>
