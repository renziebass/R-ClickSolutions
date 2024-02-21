<?php
include('user_session.php');

$isMob = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "mobile")); 
if($isMob){ 
  $newTR_loc = "cashier_new_transaction";
}else{ 
  $newTR_loc = "cashier_new_transaction2";
}

if((empty($_GET['id']))) {
  header("Location: cashier_unpaid_transactions.php");
}

$sql1="SELECT   
tb_transactions.name,
SUM(tb_cart.quantity) AS items,
SUM(tb_cart.total) AS total,
CONCAT(DATE_FORMAT(tb_transactions.date,'%M %d,%Y'),'  ',tb_transactions.time) AS date_time
FROM tb_cart 
JOIN tb_transactions ON tb_cart.transaction_id=tb_transactions.id
WHERE tb_transactions.id='" .$_GET['id']. "'
GROUP BY tb_cart.transaction_id";
$result1=mysqli_query($db,$sql1);
$row1 = mysqli_fetch_assoc($result1);

$sql="SELECT *
FROM (SELECT
      tb_products.id,
      tb_products.product_brand,
      tb_products.category,
      CONCAT(tb_products.mc_brand,'-',tb_products.mc_model,'-',tb_products.category) AS specification,
      tb_products.price
      FROM tb_cart LEFT JOIN tb_products ON tb_cart.product_id=tb_products.id) AS A
JOIN (SELECT
      tb_cart.product_id,
      SUM(tb_cart.quantity) as quantity,
      SUM(tb_cart.price*tb_cart.quantity) AS total
      FROM tb_cart WHERE tb_cart.transaction_id='" .$_GET['id']. "'
GROUP BY tb_cart.product_id) AS B
ON A.id=B.product_id
GROUP BY B.product_id";
                                                      
$result = mysqli_query($db,$sql);

if (mysqli_num_rows($result) > 0) {

} else {

  $sql4="DELETE FROM tb_transactions WHERE tb_transactions.id='" .$_GET['id']. "'";
  $sql5="DELETE FROM tb_payments
  WHERE tb_payments.id='" .$_GET['id']. "'";
  if (($db->query($sql4)) && ($db->query($sql5)) === TRUE) {
  header("Location: cashier_unpaid_transactions.php");
  } 
}
?>
<!doctype html>
<html lang="en" data-bs-theme="auto">
  <head><script src="../assets/js/color-modes.js"></script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.111.3">
    <title>TR# : <?php echo $_GET['id']; ?> </title>
 
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
            <a class="nav-link" aria-current="page" href="cashier_dashboard.php">
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
            <a class="nav-link active" href="cashier_unpaid_transactions.php">
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
          <button class="btn btn-success me-1" type="button"
          onclick="btn_modify(this.getAttribute('data-1'),this.getAttribute('data-2'),this.getAttribute('data-3'))"
          data-1="<?php echo $row1['name']; ?>"
          data-2="<?php echo $_GET['id']; ?>"
          data-3="<?php echo date("Y-m-d"); ?>">
         <span data-feather="edit" class="align-text-bottom"></button>
         
    </div>

      <div class="table" id="page">
       <div class="">
        <div class="row">

          <div class="col text-center">
            <div class="card-body p-2">
 
                <h6 class="mt-0" title="Number of Customers"><?php
      if(empty($row1['date_time'])) {
      $date_time ="N/A";
      } else {
      $date_time = $row1['date_time'];
      }

      if(empty($row1['name']) && ($_GET['id'])) {
        echo "No Products, Please VOID this TRANSACTION";
      } else {
        echo "".$row1['name']." -".$_GET['id']." (".$date_time.")";
      } 
      ?></h6>
                <h3 class="text-danger"><?php
            if(empty($row1['total'])) {
            $payment ="0";
            } else {
            $payment = $row1['total'];
            }
            echo $payment;
            ?></h3>
                <p class="mb-0 text-muted">
                <span class="text-danger fw-bold"><?php
            if(empty($row1['items'])) {
            $items ="0";
            } else {
            $items = $row1['items'];
            }
            echo $items;
            ?></span>
                <span class="text-nowrap">Items</span>  
                </p>
            </div>
          </div>

        </div>
      </div>

      <h6 class="mt-3 text-center text-muted">Products</h6>
        <table class="table table-hover table-sm table-borderless">
          <thead>
          <tr class="text-muted">
              <th scope="col">Specification</th>
              <th scope="col">QTY</th>
              <th scope="col">SRP</th>
              <th scope="col">Total</th>
              <th scope="col"></th>
            </tr>
          </thead>
          <tbody>
            <?php
                $sql="SELECT *
                FROM (SELECT
                      tb_products.id,
                      tb_products.product_brand,
                      tb_products.category,
                      CONCAT(tb_products.category,' ',tb_products.product_brand,' ',tb_products.mc_brand,' ',tb_products.mc_model) AS specification,
                      tb_products.price
                      FROM tb_cart LEFT JOIN tb_products ON tb_cart.product_id=tb_products.id) AS A
                JOIN (SELECT
                      tb_cart.product_id,
                      SUM(tb_cart.quantity) as quantity,
                      SUM(tb_cart.price*tb_cart.quantity) AS total
                      FROM tb_cart WHERE tb_cart.transaction_id='" .$_GET['id']. "'
                GROUP BY tb_cart.product_id) AS B
                ON A.id=B.product_id
                GROUP BY B.product_id";
                                                                                    
                $result = mysqli_query($db,$sql);

                if (mysqli_num_rows($result) > 0) 
                {
                foreach($result as $items)
                {
            ?>
            <tr>
                
                <td><?php echo $items['specification']; ?></td>
                <td><?php echo $items['quantity']; ?></td>
                <td><?php echo $items['price']; ?></td>
                <td><?php echo $items['total']; ?></td>

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

<link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@4/dark.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<script>
    function btn_modify(data_1,data_2,data_3) {
      const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
          confirmButton: "btn btn-success",
          cancelButton: "btn btn-secondary me-1"
        },
        buttonsStyling: false
      });
      swalWithBootstrapButtons.fire({
        title: "MODIFY "+data_1+ " ?",
        showCancelButton: true,
        confirmButtonText: "Modify",
        cancelButtonText: "Cancel",
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          location.href='cashier_new_transaction2.php?id='+data_2+'&date='+data_3;
        } else {
          result.dismiss === Swal.DismissReason.cancel
        }
      });
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/feather-icons@4.28.0/dist/feather.min.js" integrity="sha384-uO3SXW5IuS1ZpFPKugNNWqTZRRglnUJK6UAZ/gxOX80nxEkN9NcGZTftn6RzhGWE" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</script><script src="dashboard.js"></script>
  </body>
</html>
