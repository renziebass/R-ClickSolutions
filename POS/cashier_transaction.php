<?php
include('user_session.php');
if((empty($_GET['id']))) {
  header("Location: cashier_transactions.php?date=".date("Y-m-d")."");
}

if((empty($_GET['DeleteProduct'])) && (empty($_GET['QTY']))) {
} else {
$sql2="DELETE FROM tb_cart WHERE tb_cart.product_id='" .$_GET['DeleteProduct']. "' AND tb_cart.transaction_id='" .$_GET['id']. "'";
$sql3="UPDATE tb_products
SET tb_products.available=tb_products.available+'" .$_GET['QTY']. "'
WHERE tb_products.id='" .$_GET['DeleteProduct']. "'";
if (($db->query($sql2)) && ($db->query($sql3)) === TRUE) {
  echo "Record updated successfully";
  header("Location: cashier_transaction.php?id=".$_GET['id']."");
} else {
  echo "Error updating record: " . $db->error;
}
}


$sql1="SELECT 
SUM(tb_cart.quantity) AS items,
tb_payments.total,
CONCAT(DATE_FORMAT(tb_transactions.date,'%M %d,%Y'),'  ',tb_transactions.time) AS date_time,
tb_transactions.date,
CONCAT(FORMAT(tb_payments.payment, 2)) AS payment,
tb_payments.change1
FROM tb_cart LEFT JOIN tb_transactions ON tb_cart.transaction_id=tb_transactions.id
RIGHT JOIN tb_payments ON tb_transactions.id=tb_payments.id 
WHERE tb_cart.transaction_id='" .$_GET['id']. "'
GROUP BY tb_cart.transaction_id";
$result1=mysqli_query($db,$sql1);
$row1 = mysqli_fetch_assoc($result1);

$tr_date=date_create($row1['date']);
$cur_date=date_create(date("Y-m-d"));
$diff=date_diff($tr_date,$cur_date);

if($diff->format('%a') === '0'){
  $btn_void=null;
} else {
  $btn_void="hidden";
}

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
    header("Location: cashier_transactions.php?date=".date("Y-m-d")."");
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
      
    

  
      <h6 class="text-muted mt-0 text-center">
        <?php
          echo $diff->format('%a day(s) ago');
        ?>
      </h6>
      <div class="table" id="page">
      <h6 class="text-center">
      <?php
      if(empty($row1['date_time'])) {
      $date_time ="N/A";
      } else {
      $date_time = $row1['date_time'];
      }
      $det =$_GET['id'];
      echo "$det ($date_time)" ;
      ?></h6>
      <div class="">
        <div class="row">

          <div class="col text-center m-1">
            <div class="card-body p-2">
 
                <h6 class="text-muted fw-normal mt-0" title="Number of Customers">Total</h6>
                <h3 class="text-success"><?php
            if(empty($row1['total'])) {
            $payment ="0";
            } else {
            $payment = $row1['total'];
            }
            echo $payment;
            ?></h3>
                <p class="mb-0 text-muted">
                <span class="text-success fw-bold"><?php
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

          <div class="col text-center m-1">
            <div class="card-body p-2">

                <h6 class="text-muted fw-normal mt-0" title="Number of Customers">Payment</h6>
                <h3 class="text-primary"><?php
                if(empty($row1['payment'])) {
                $payment ="0";
                } else {
                $payment = $row1['payment'];
                }
                echo $payment;
                ?></h3>
                <p class="mb-0 text-muted">
                <span class="text-danger fw-bold"> <?php
                if(empty($row1['change1'])) {
                $change1 ="0";
                } else {
                $change1 = $row1['change1'];
                }
                echo $change1;
                ?></span>
                <span class="text-nowrap">Change</span>  
                </p>
            </div>
          </div>
          
        </div>
      </div>

      <h6 class="mt-3 text-center text-muted">Products</h6>
        <table class="table table-hover table-sm">
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
                      CONCAT(tb_products.category,' ',tb_products.product_brand,' ',tb_products.mc_brand,' ',tb_products.mc_model) AS specification
                      FROM tb_cart LEFT JOIN tb_products ON tb_cart.product_id=tb_products.id) AS A
                JOIN (SELECT
                      tb_cart.product_id,
                      SUM(tb_cart.quantity) as quantity,
                      SUM(tb_cart.price*tb_cart.quantity) AS total,
                      tb_cart.price
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
                <td>
        
                  <button type="button" class="btn btn-sm p-0 m-0"
                    onclick="btn_void(this.getAttribute('data-1'), this.getAttribute('data-2'), 
                    this.getAttribute('data-3'))"
                    data-1="<?php echo $items['id']; ?>"
                    data-2="<?php echo $items['specification']; ?>"
                    data-3="<?php echo $items['quantity']; ?>"
                    <?php echo $btn_void?>>
                    <span>
                      <svg  class="text-danger" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-square-fill" viewBox="0 0 16 16">
                      <path d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm3.354 4.646L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 1 1 .708-.708z"/>
                      </svg>
                    </span>
                  </button>

             

                </td>
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
  function btn_void(data_1,data_2,data_3) {
      const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
          confirmButton: "btn btn-danger",
          cancelButton: "btn btn-secondary me-1"
        },
        buttonsStyling: false
      });
      swalWithBootstrapButtons.fire({
        title: "VOID PRODUCT ?",
        footer: '<h6 class="text-white text-center">'+data_2+'</h6>',
        showCancelButton: true,
        confirmButtonText: "Void",
        cancelButtonText: "Cancel",
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = window.location.href+'&DeleteProduct='+data_1+'&QTY='+data_3;
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

