
<?php
include('user_session.php');
$sql1="SELECT
COUNT(tb_payments.id)AS transactions
FROM tb_payments
WHERE MONTH(tb_payments.date)='".$_GET['m']."' AND YEAR(tb_payments.date)='".$_GET['y']."'";
$result1=mysqli_query($db,$sql1);
$row1 = mysqli_fetch_assoc($result1);

$sql2="SELECT
SUM(tb_cart.quantity)AS items
FROM tb_payments
LEFT JOIN tb_cart ON tb_payments.id=tb_cart.transaction_id
WHERE MONTH(tb_payments.date)='".$_GET['m']."' AND YEAR(tb_payments.date)='".$_GET['y']."'
GROUP BY year(tb_payments.date),month(tb_payments.date)
ORDER BY year(tb_payments.date),month(tb_payments.date) DESC";
$result2=mysqli_query($db,$sql2);
$row2 = mysqli_fetch_assoc($result2);

$sql3="SELECT
CONCAT(FORMAT(SUM(tb_cart.price*tb_cart.quantity), 2)) AS paid
FROM tb_payments
LEFT JOIN tb_cart ON tb_payments.id=tb_cart.transaction_id
WHERE MONTH(tb_payments.date)='".$_GET['m']."' AND YEAR(tb_payments.date)='".$_GET['y']."'
GROUP BY year(tb_payments.date),month(tb_payments.date)
ORDER BY year(tb_payments.date),month(tb_payments.date) DESC";
$result3=mysqli_query($db,$sql3);
$row3 = mysqli_fetch_assoc($result3);

$sql4="SELECT
FORMAT(SUM(tb_payments.total) / 24, 2) AS daily
FROM tb_payments
WHERE MONTH(tb_payments.date)='".$_GET['m']."' AND YEAR(tb_payments.date)='".$_GET['y']."'";
$result4=mysqli_query($db,$sql4);
$row4 = mysqli_fetch_assoc($result4);

$sql5="SELECT
FORMAT(SUM(tb_products.capital*tb_cart.quantity) , 2) AS capital,
FORMAT(SUM(tb_cart.price*tb_cart.quantity - tb_products.capital*tb_cart.quantity) , 2) AS profit
FROM tb_transactions
INNER JOIN tb_cart ON tb_transactions.id=tb_cart.transaction_id
LEFT JOIN tb_products ON tb_cart.product_id=tb_products.id
WHERE tb_transactions.status='paid' AND MONTH(tb_transactions.date)='".$_GET['m']."' AND YEAR(tb_transactions.date)='".$_GET['y']."';";
$result5=mysqli_query($db,$sql5);
$row5 = mysqli_fetch_assoc($result5);
?>
<!doctype html>
<html lang="en" data-bs-theme="auto">
  <head><script src="../assets/js/color-modes.js"></script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.111.3">
    <title><?php 
      $month = $_GET['m'];
      $year = $_GET['y'];
      
      $date=date_create();
      date_date_set($date,$year,$month,1);
      echo date_format($date,"F Y");
      ?></title>
 
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
  
  

    
<header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
  <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="#">R-Click Solutions POS: <i><?php echo $company_name; ?></i></a>
  <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
 
</header>

<div class="container-fluid">
  <div class="row">
  <?php
  include 'admin_navbar.php';
  ?>

    <main class="col-md-9 ms-sm-auto col-lg-10">
    <div class="d-flex justify-content-end mt-3 mb-3">
    <button class="btn btn-secondary" onclick="printDiv();"type="button"><span data-feather="printer" class="align-text-bottom"></button>
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
      <div class=""id="page">
      <div class="col">
        <h6 class="text-center m-3"><?php 
      $month = $_GET['m'];
      $year = $_GET['y'];
      
      $date=date_create();
      date_date_set($date,$year,$month,1);
      echo date_format($date,"F Y");
      ?></h6>
      </div>
      <div class="row">
      <div class="col text-start p-2 ">
            <div class="card-body p-2 border-start border-4 shadow rounded border-success bg-white">
            <div class="row">
              <p class="col m-0 text-success">
                RECEIPTS
              </p>
              <p class="col m-0 d-flex justify-content-end text-success">
              <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" fill="currentColor" class="bi bi-person-lines-fill" viewBox="0 0 16 16">
                    <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zM11 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5m.5 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1zm2 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1zm0 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1z"/>
                  </svg>
              </p>
            </div>
          
                <h4 class="text-success">
                <?php echo $row1['transactions'];?>
                </h4>
                <p class="mb-0 text-muted">
                <span class="text-success fw-bold">
                <?php
                  $items=null;
                  if (empty($row2['items'])) {
                    $items = 0;
                  } else {
                    $items = $row2['items'];
                  }
                  echo $items;
                  ?>
                </span>
                <span class="text-nowrap">Products Sold</span> 
                </p>
                <p class="mb-0 text-muted">
                <span class="text-success fw-bold"><?php echo $row4['daily'];?></span>
                <span class="text-nowrap">Daily Avg</span> 
                </p>
            </div>
          </div>

          <div class="col text-start p-2">
            <div class="card-body p-2 border-start border-4 shadow rounded border-primary bg-white">
            <div class="row">
              <p class="col m-0 text-primary">
                REVENUE 
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
                <h4 class="text-primary"><span>&#8369;</span><?php
                  $paid=null;
                  if (empty($row3['paid'])) {
                    $paid = 0;
                  } else {
                    $paid = $row3['paid'];
                  }
                  echo $paid;
                  ?>
                </h4>
                <p class="mb-0 text-muted">
                <span class="text-primary fw-bold"><span>&#8369;</span><?php 
                  $capital=null;
                  if (empty($row5['capital'])) {
                    $capital = 0;
                  } else {
                    $capital = $row5['capital'];
                  }
                  echo $capital;
                  ?></span>
                <span class="text-nowrap me-2">Capital</span>
                <br>
                <span class="text-primary fw-bold"><span>&#8369;</span><?php
                  $profit=null;
                  if (empty($row5['profit'])) {
                    $profit = 0;
                  } else {
                    $profit = $row5['profit'];
                  }
                  echo $profit;
                  ?>
                </span>
                <span class="text-nowrap">Net Profit</span>  
                </p>
            </div>
          </div>
      </div>
     

          <div class="col text-center">
          <table class="table table-hover table-sm mt-3">
          <thead>
            <tr class="text-muted">
              <th scope="col">Date</th>
              <th scope="col">Customers</th>
              <th scope="col">Items Sold</th>
              <th scope="col">Sales</th>
            </tr>
          </thead>
          <tbody>
            <?php
                $sql="SELECT *
                FROM (SELECT
                        tb_payments.date,
                        DATE_FORMAT(tb_payments.date,'%M %d,%Y') AS date1,
                        FORMAT(SUM(tb_payments.total),2) AS amount,
                        COUNT(tb_payments.payment) AS customers
                        FROM tb_payments
                        WHERE MONTH(tb_payments.date)='".$_GET['m']."' AND YEAR(tb_payments.date)='".$_GET['y']."'
                       GROUP BY tb_payments.date) AS A
                JOIN (SELECT
                      SUM(tb_cart.quantity) AS items,
                      CONCAT(FORMAT(SUM(tb_cart.price*tb_cart.quantity), 2)) AS amount2,
                      DATE_FORMAT(tb_transactions.date,'%M %d,%Y') AS date1
                      FROM tb_transactions
                      JOIN tb_cart ON tb_transactions.id=tb_cart.transaction_id
                      WHERE tb_transactions.status='paid'
                      GROUP BY tb_transactions.date) AS B
                ON A.date1=B.date1
                ORDER BY A.date DESC";
                                                                                    
                $result = mysqli_query($db,$sql);

                if (mysqli_num_rows($result) > 0) 
                {
                foreach($result as $items)
                {
            ?>
            <tr onclick="location.href='admin_transactions.php?date=<?php echo $items['date'];?>'">  
                <td><?php echo $items['date1']; ?></td>
                <td><?php echo $items['customers']; ?></td>
                <td><?php echo $items['items']; ?></td>
                <td><?php echo $items['amount']; ?></td>
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

  
    </main>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/feather-icons@4.28.0/dist/feather.min.js" integrity="sha384-uO3SXW5IuS1ZpFPKugNNWqTZRRglnUJK6UAZ/gxOX80nxEkN9NcGZTftn6RzhGWE" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</script><script src="dashboard.js"></script>
  </body>
</html>
