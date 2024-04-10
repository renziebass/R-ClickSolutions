
<?php
include('user_session.php');

$sql1="SELECT
COUNT(tb_transactions.id)AS transactions
FROM tb_transactions
WHERE tb_transactions.status='unpaid'";
$result1=mysqli_query($db,$sql1);
$row1 = mysqli_fetch_assoc($result1);

$sql2="SELECT
SUM(tb_cart.quantity) AS items
FROM tb_transactions
JOIN tb_cart ON tb_transactions.id=tb_cart.transaction_id
WHERE tb_transactions.status='unpaid';";
$result2=mysqli_query($db,$sql2);
$row2 = mysqli_fetch_assoc($result2);

$sql3="SELECT
CONCAT(FORMAT(SUM(tb_cart.total), 2)) AS unpaid
FROM tb_transactions
JOIN tb_cart ON tb_transactions.id=tb_cart.transaction_id
WHERE tb_transactions.status='unpaid'";
$result3=mysqli_query($db,$sql3);
$row3 = mysqli_fetch_assoc($result3);
?>
<!doctype html>
<html lang="en" data-bs-theme="auto">
  <head><script src="../assets/js/color-modes.js"></script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.111.3">
    <title>UNPAID TRANSANCTIONS</title>
 
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
  include 'cashier_navbar.php';
  ?>

    <main class="col-md-9 ms-sm-auto col-lg-10">
    <div class="d-flex justify-content-end mt-3 mb-3">
      <button class="btn btn-secondary me-1" onclick="printDiv();"type="button"><span data-feather="printer" class="align-text-bottom"></button>
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
    <div class="" id="page">
    <h6 class="text-center m-2">Amount Receivable</h6>
        <div class="row">

          <div class="col text-start p-2">
            <div class="card-body p-2 border-start border-4 shadow rounded border-danger bg-white">
                <div class="row">
                  <p class="col m-0 text-danger">
                    TOTAL
                  </p>
                  <p class="col m-0 d-flex justify-content-end text-danger">
                  <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" fill="currentColor" class="bi bi-person-lines-fill" viewBox="0 0 16 16">
                    <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zM11 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5m.5 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1zm2 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1zm0 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1z"/>
                  </svg>
                  </p>
                </div>
                <h4 class="text-danger"><span>&#8369;<?php
                  $unpaid=null;
                  if (empty($row3['unpaid'])) {
                    $unpaid = 0;
                  } else {
                    $unpaid = $row3['unpaid'];
                  }
                  echo $unpaid;
                  ?></span>
                </h4>
                <p class="mb-0 text-muted">
                <span class="text-danger fw-bold"><?php echo $row1['transactions'];?></span>
                <span class="text-nowrap me-2">Accounts</span>
                <br>
                <span class="text-danger fw-bold">
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
                <span class="text-nowrap">Items</span> 
                </p>
            </div>
          </div>
          
        </div>
        <div class="row">

        <div class="col text-center p-2">
            <div class="card-body p-2">
                  <p class=" m-0">
                    ACOUNTS
                  </p>
           
     

        <table class="table table-hover table-sm mb-5" style="margin-bottom: 70px;">
        <thead>
            <tr class="text-muted">
              <th scope="col">Name</th>
              <th scope="col">Date Time</th>
              <th scope="col">Items</th>
              <th scope="col">Amount</th>
            </tr>
          </thead>
          <tbody>
            <?php
                $sql="SELECT *
                FROM (SELECT
                    tb_transactions.id,
                    tb_transactions.name,
                    tb_transactions.date
                    FROM tb_transactions WHERE tb_transactions.status='unpaid') AS A
                JOIN (SELECT
                    tb_cart.transaction_id,
                    SUM(tb_cart.quantity) as items,
                    SUM(tb_cart.price*tb_cart.quantity) AS total
                        FROM tb_cart
                    GROUP BY tb_cart.transaction_id) AS B
                ON A.id=B.transaction_id
                GROUP BY A.id
                ORDER BY A.date DESC";
                                                                                    
                $result = mysqli_query($db,$sql);

                if (mysqli_num_rows($result) > 0) 
                {
                foreach($result as $items)
                {
            ?>
            <tr onclick="location.href='cashier_unpaid_transaction.php?id=<?php echo $items['id'];?>'">  
                <td><?php echo $items['name']; ?></td>
                <td><?php echo $items['date']; ?></td>
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
