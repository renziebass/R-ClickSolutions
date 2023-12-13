<?php
include('user_session.php');
$sql1 = "SELECT
tb_products.id,
CONCAT(tb_products.category,' ',tb_products.product_brand,' ',tb_products.mc_brand,' ',tb_products.mc_model)
AS specification
FROM tb_products";
$search = "renzie";

$sql2="SELECT
SUM(tb_cart.quantity) AS items,
SUM(tb_cart.price*tb_cart.quantity) AS total2,
FORMAT(SUM(tb_cart.price*tb_cart.quantity), 2) AS total,
tb_transactions.name
FROM tb_cart
LEFT JOIN tb_transactions ON tb_cart.transaction_id=tb_transactions.id
WHERE tb_cart.transaction_id='" .$_GET['id']. "'
GROUP BY tb_cart.transaction_id";
$result2=mysqli_query($db,$sql2);
$row2 = mysqli_fetch_assoc($result2);

$result1=mysqli_query($db,$sql1);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (!empty($_POST["search"])) {
    $search = $_POST["search"];
    header("Cache-Control: no cache");
  } else {
    $search = null;
  }
}

function validateInput($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  if (empty($_POST["product_id"])) {
    $product_id_err = "* ";
  } else {
    $product_id = validateInput($_POST["product_id"]);
  }
  if (empty($_POST["quantity"])) {
    $quantity_err = "* ";
  } else {
    $quantity = validateInput($_POST["quantity"]);
  }
  if (empty($_POST["price"])) {
    $price_err = "* ";
  } else {
    $price = validateInput($_POST["price"]);
    $total = $price * $quantity;
  }

  if (empty($_POST["product_id2"])) {
    $product_id2_err = "* ";
  } else {
    $product_id2 = validateInput($_POST["product_id2"]);
  }
  if (empty($_POST["quantity2"])) {
    $quantity2_err = "* ";
  } else {
    $quantity2 = validateInput($_POST["quantity2"]);
  }
  if (empty($_POST["name"])) {
    $name_err = "* ";
  } else {
    $name = validateInput($_POST["name"]);
  }
  if (empty($_POST["payment"])) {
    $payment_err = "* ";
  } else {
    $payment = validateInput($_POST["payment"]);
  }
  
  
  if(!empty($_POST["product_id"]) && !empty($_POST["price"] && !empty($_POST["quantity"])))
  {
    try {
        $sql5check = "SELECT tb_products.available
        FROM tb_products
        WHERE tb_products.id='$product_id'";
        $resultcheck=mysqli_query($db,$sql5check);
        $itemResult = mysqli_fetch_assoc($resultcheck);

        $sql4 = "INSERT INTO `tb_cart` (`transaction_id`, `date`, `product_id`, `quantity`, `price`, `total`)
        VALUES ('" .$_GET['id']. "', '".$_GET['date']."', '$product_id', '$quantity', '$price', '$total')";

        $sql4b = "UPDATE tb_products
        SET tb_products.available=tb_products.available-'$quantity'
        WHERE tb_products.id='$product_id'";

        function function_sound() {
        echo "<script>
              var audio = new Audio('https://rclickpos.com/beep.wav');
              audio.play();
              </script>";
        header("Refresh:0");
        }

        if ($itemResult['available'] > $quantity) {
          $AddProduct = mysqli_query($db, $sql4);
          $UpdateProduct = mysqli_query($db, $sql4b);
          function_sound();

        } elseif ($itemResult['available'] >= $quantity) {
          $AddProduct = mysqli_query($db, $sql4);
          $UpdateProduct = mysqli_query($db, $sql4b);
          function_sound();

        } else {
          echo '<script>alert("Stocks not enough!")</script>';
            header("Refresh:0");
        }

    
        
    }
    catch(PDOException $e)
      {
        echo $sql1 . "<br>" . $e->getMessage();
      }
    $db=null;
  }
  if(!empty($_POST["product_id2"]) && !empty($_POST["quantity2"]))
  {
    try {

        $sql6a = "DELETE FROM `tb_cart`
        WHERE tb_cart.transaction_id='" .$_GET['id']. "'
        AND tb_cart.product_id='$product_id2'";

        $sql6b = "UPDATE tb_products
        SET tb_products.available=tb_products.available+'$quantity2'
        WHERE tb_products.id='$product_id2'";


        $DeleteProduct = mysqli_query($db, $sql6a);
        $ReturnProduct = mysqli_query($db, $sql6b);
        header("Refresh:0");
      
        
    }
    catch(PDOException $e)
      {
        echo $sql1 . "<br>" . $e->getMessage();
      }
    $db=null;
  }
  if(!empty($_POST["name"]))
  {
    try {

        $sql8 = "INSERT INTO tb_transactions (id, date,time, name, status) VALUES
        ('" .$_GET['id']. "','".$_GET['date']."','".date("H:i:s")."','" . $name . "','unpaid')";

        $SaveTR = mysqli_query($db, $sql8);

        header("refresh:0.5;url=cashier_dashboard.php");
      
        
    }
    catch(PDOException $e)
      {
        echo $sql1 . "<br>" . $e->getMessage();
      }
    $db=null;
  }
  if(!empty($_POST["payment"]))
  {
   
    
    try {

      $sqlcheck1 = "SELECT * FROM tb_transactions
      WHERE tb_transactions.id='" .$_GET['id']. "'";
      $TRcheck = mysqli_query($db, $sqlcheck1);
      $resultCheck1 = mysqli_fetch_assoc($TRcheck);

      $sqlcheck2 = "SELECT * FROM tb_payments
      WHERE tb_payments.id='" .$_GET['id']. "'";
      $Paymentcheck = mysqli_query($db, $sqlcheck2);
      $resultCheck2 = mysqli_fetch_assoc($Paymentcheck);

      if(empty($resultCheck1) && empty($resultCheck2))  {

        $sql9="INSERT INTO tb_payments (id, date,time, total, payment, change1)
        VALUES ('" .$_GET['id']. "','".$_GET['date']."','".date("H:i:s")."','" . $row2['total'] . "','" . $_POST["payment"] . "','" . $_POST["payment"]-$row2['total2'] . "')";

        $sql10 = "INSERT INTO tb_transactions (id, date,time, name, status) VALUES
        ('" .$_GET['id']. "','".$_GET['date']."','".date("H:i:s")."','','paid')";


        if ($_POST["payment"] > $row2['total2']) {

        $change=$_POST["payment"]-$row2['total2'];
        $SaveTR2a = mysqli_query($db, $sql9);
        $SaveTR2b = mysqli_query($db, $sql10);
        function function_alert($message) {
        echo "<script>alert('Change is P $message');</script>";
        }
        function_alert($change);
        header( "refresh:0.5;url=cashier_dashboard.php" );
        } elseif ($_POST["payment"] >= $row2['total2']) {

        $change="NO CHANGE";
        $SaveTR2a = mysqli_query($db, $sql9);
        $SaveTR2b = mysqli_query($db, $sql10);
        function function_alert($message) {
        echo "<script>alert('$message');</script>";
        }
        function_alert($change);
        header( "refresh:0.5;url=cashier_dashboard.php" );
        } else {

        function function_alert($message) {
        echo "<script>alert('Payment not enought! Total is $message');</script>";
        }
        function_alert($row2['total2']);
        header("Refresh:0");

        }
        
      } else {

        $sql9="INSERT INTO tb_payments (id, date,time, total, payment, change1)
        VALUES ('" .$_GET['id']. "','".$_GET['date']."','".date("H:i:s")."','" . $row2['total'] . "','" . $_POST["payment"] . "','" . $_POST["payment"]-$row2['total2'] . "')";

        $sql10 = "UPDATE tb_transactions
        SET tb_transactions.status='paid',tb_transactions.date='".$_GET['date']."'
        WHERE tb_transactions.id='" .$_GET['id']. "'";


        if ($_POST["payment"] > $row2['total2']) {

        $change=$_POST["payment"]-$row2['total2'];
        $SaveTR2a = mysqli_query($db, $sql9);
        $SaveTR2b = mysqli_query($db, $sql10);
        function function_alert($message) {
        echo "<script>alert('Change is P $message');</script>";
        }
        function_alert($change);
        header( "refresh:0.5;url=cashier_dashboard.php" );
        } elseif ($_POST["payment"] >= $row2['total']) {

        $change="NO CHANGE";
        $SaveTR2a = mysqli_query($db, $sql9);
        $SaveTR2b = mysqli_query($db, $sql10);
        function function_alert($message) {
        echo "<script>alert('$message');</script>";
        }
        function_alert($change);
        header( "refresh:0.5;url=cashier_dashboard.php" );
        } else {

        function function_alert($message) {
        echo "<script>alert('Payment not enought! Total is $message');</script>";
        }
        function_alert($row2['total2']);
        header("Refresh:0");

        }

      }

      
 
    }
    catch(PDOException $e)
      {
        echo $sql1 . "<br>" . $e->getMessage();
      }
    $db=null;
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
    <title>NEW TRANSACTION</title>
 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">

    <style>
     
      .datalistOptions {
        width: 100%;
      }
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

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
      <h5>New Transaction</h5>
       
      </div>
      <div class="table">
        <div class="text-center border-top border-end border-start" style="background-color: #CCEEBC">
            <div class="row ">
              <div class="col">
                <p class="m-0 p-0 fw-bold text-primary"><?php
                  if(empty($row2['name'])) {
                  $SaveButton = null;
                  $name ="N/A";
                  } else {
                  $name = $row2['name'];
                  $SaveButton = "disabled";
                  }
                  echo $name;
                  ?></p>
                <p class="m-0 p-0 text-muted">Name</p>
              </div>
              <div class="col" class="trigger">
                <p class="m-0 p-0 fw-bold text-primary"><?php echo $_GET['id'];?></p>
                <p class="m-0 p-0 text-muted">TR #</p>
              </div>
              <div class="col">
                <p class="m-0 p-0 fw-bold text-primary"><?php
                  if(empty($row2['items'])) {
                  $items ="0";
                  } else {
                  $items = $row2['items'];
                  }
                  echo $items;
                  ?></p>
                <p class="m-0 p-0 text-muted">Total Items</p>
              </div>
              <div class="col">
                <p class="m-0 p-0 fw-bold text-primary">P <?php
                  if(empty($row2['total'])) {
                  $total ="0";
                  } else {
                  $total = $row2['total'];
                  }
                  echo $total;
                  ?></p>
                <p class="m-0 p-0 text-muted">Total Amount</p>
              </div>
            </div>
        </div>
      <div class="text-start border-bottom border-end border-start p-2" style="background-color: #CCEEBC">
        
      <table class="table table-hover table-sm">
          <thead>
            <tr>
              <th scope="col">Specification</th>
              <th scope="col">QTY</th>
              <th scope="col">SRP</th>
              <th scope="col">Total</th>
              <th scope="col"></th>
            </tr>
            </tr>
          </thead>
          <tbody>
            <?php
                $sql="SELECT *
                FROM (SELECT
                    tb_products.id,
                    CONCAT(tb_products.category,' ',tb_products.product_brand,' ',tb_products.mc_brand,' ',tb_products.mc_model)AS specification,
                    FORMAT(tb_products.price, 2) AS price
                    FROM tb_cart LEFT JOIN tb_products ON tb_cart.product_id=tb_products.id) AS A
                JOIN (SELECT
                    tb_cart.product_id,
                    SUM(tb_cart.quantity) AS quantity,
                    FORMAT(SUM(tb_cart.price*tb_cart.quantity), 2) AS total
                    FROM tb_cart WHERE tb_cart.transaction_id='".$_GET['id']."'
                    GROUP BY tb_cart.product_id) AS B
                ON A.id=B.product_id
                GROUP BY B.product_id";
                                                                                    
                $result = mysqli_query($db,$sql);
                
                if (mysqli_num_rows($result) > 0) 
                {
                  $button="";
                foreach($result as $items)
                {
            ?>
            <tr>
                <td><?php echo $items['specification']; ?></td>
                <td><?php echo $items['quantity']; ?></td>
                <td><?php echo $items['price']; ?></td>
                <td><?php echo $items['total']; ?></td>
                <td></td>
                <td>
        
                  <button type="button" class="btn btn-sm p-0 m-0" data-bs-toggle="modal" data-bs-target="#exampleModal2" data-bs1="<?php echo $items['id']; ?>" data-bs2="DELETE & RETURN <?php echo $items['specification']; ?>?" data-bs3="<?php echo $items['quantity']; ?>">
                    <span>
                      <svg  class="text-danger" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-square-fill" viewBox="0 0 16 16">
                      <path d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm3.354 4.646L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 1 1 .708-.708z"/>
                      </svg>
                    </span>
                  </button>

                  <div class="modal fade" id="exampleModal2" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content">
                        <div class="modal-header">
                        <h1 class="modal-title fs-5 text-danger" id="exampleModalLabel"></h1>
                        </div>
                        <div class="modal-body">
                            <form method="post" enctype="multipart/form-data">
                              <div class="mb-3">

                                <input type="hidden" id="product_id2" name="product_id2" class="form-control">
                                <input type="hidden" id="quantity2" name="quantity2" class="form-control">
                                
                              </div>
                            
                          </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" class="btn btn-sm btn-danger">REMOVE</button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>

                </td>
            </tr>
            <?php
            } 
            } else {
              $button="disabled";
              
            }
            ?>
          </tbody>
          <script>
            const exampleModal = document.getElementById('exampleModal2')
            if (exampleModal) {
              exampleModal.addEventListener('show.bs.modal', event => {
                // Button that triggered the modal
                const button = event.relatedTarget
                // Extract info from data-bs-* attributes
                const recipient = button.getAttribute('data-bs1')
                const recipient2 = button.getAttribute('data-bs2')
                const recipient3 = button.getAttribute('data-bs3')
                // If necessary, you could initiate an Ajax request here
                // and then do the updating in a callback.

                // Update the modal's content.
                const modalTitle = exampleModal.querySelector('.modal-title')
                const modalBodyInput1 = document.getElementById('product_id2')
                const modalBodyInput2 = document.getElementById('quantity2')
            

                modalTitle.textContent = `${recipient2}`
                modalBodyInput1.value = recipient
                modalBodyInput2.value = recipient3
              })
            }
          </script>
        </table>
      </div>
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center m-2">
      <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#exampleModal4" <?php echo $button;?> <?php echo $SaveButton;?>>SAVE</button>
      <div class="modal fade" id="exampleModal4" tabindex="-1" aria-labelledby="exampleModalLabel2" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                          <div class="modal-header">
                          <h6 class="modal-title" id="exampleModalLabel">SAVE TRANSACTION</h6>
                          </div>
                          <div class="modal-body">
                            <form method="post" enctype="multipart/form-data">
                              <div class="mb-3">
                                
                              <label for="validationDefault01" class="form-label">Unpaid Transaction: Customer Name</label>
                              <input onkeyup="this.value = this.value.toUpperCase();" type="text" name="name" class="form-control" id="validationDefault01" required>
                                
                              </div>
                            
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-danger">Save</button>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exampleModal5" <?php echo $button;?>>PAY</button>
      <div class="modal fade" id="exampleModal5" tabindex="-1" aria-labelledby="exampleModalLabel2" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                          <div class="modal-header">
                          <h6 class="modal-title" id="exampleModalLabel">PAY TRANSACTION</h6>
                          </div>
                          <div class="modal-body">
                            <form method="post" enctype="multipart/form-data">
                              <div class="mb-3">
                                
                              <label for="validationDefault01" class="form-label">Payment Amount</label>
                              <input type="number" name="payment" class="form-control" id="validationDefault01" required>
                                
                              </div>
                            
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-success">PAY</button>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
           
      </div>


      
            
      <h6 class="mt-3">Add Products</h6>
      <form method="post" enctype="multipart/form-data">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mt-3">
          <div class="input-group">
            <input id="search" name="search" class="form-control" list="datalistOptions" id="exampleDataList" placeholder="Type to search product...">
            <script>
              window.onload = init;
              function init(){
              document.getElementById("search").focus();
              }
            </script>
            <datalist id="datalistOptions">
              <?php while($row1 = mysqli_fetch_array($result1)):;?>
              <option value="<?php echo $row1['id'];?>"><?php echo $row1['specification'];?></option>
              <?php endwhile; ?>
            </datalist>
            <script>
              window.onload = init;
              function init(){
              document.getElementById("search").focus();
              }
            </script>
            <button class="btn btn-secondary" type="submit" id="button-addon2">SEARCH <span data-feather="search" class="align-text-end"></button>
          </div>
      </div>
      </form>
      <h6>
        <?php
         if(empty($_POST["search"])) {
          $text = "";
          } else {
          $text = "Result for the keyword ' ".$search." '";
          }
          echo $text;
        ?>
      </h6>
        <table class="table table-hover table-sm">
          <thead>
            <tr>
              <th scope="col">Specification</th>
              <th scope="col">Stocks</th>
              <th scope="col">SRP</th>
            </tr>
          </thead>
          <tbody>
            <?php
                $sqlb="SELECT
                tb_products.id,
                tb_products.product_brand,
                CONCAT(tb_products.category,' ',tb_products.product_brand,' ',tb_products.mc_brand,' ',tb_products.mc_model) AS specification,
                CONCAT(tb_products.available,'/',tb_products.stocks) AS stocks,
                CONCAT(tb_products.price) AS price,
                CASE WHEN tb_products.available=0
                THEN 'text-danger' ELSE null END AS textcolor
                FROM tb_products
                WHERE tb_products.category LIKE '%".$search."%' 
                OR tb_products.mc_brand LIKE '%".$search."%' 
                OR tb_products.mc_model LIKE '%".$search."%' 
                OR tb_products.product_brand LIKE '%".$search."%'
                OR tb_products.id LIKE '%".$search."%'";
                                                                                    
                $resultb = mysqli_query($db,$sqlb);

                if (mysqli_num_rows($resultb) > 0) 
                {
                foreach($resultb as $itemsb)
                {
            ?>
            <tr class="<?php echo $itemsb['textcolor']; ?>" data-bs-toggle="modal" data-bs-target="#exampleModal3" data-bs-whatever="<?php echo $itemsb['specification']; ?>" data-bs-whatever2="<?php echo $itemsb['id']; ?>" data-bs-whatever3="<?php echo $itemsb['price']; ?>"> 
            <span>
                  <div class="modal fade" id="exampleModal3" tabindex="-1" aria-labelledby="exampleModalLabel2" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                          <div class="modal-header">
                          <h1 class="modal-title fs-5" id="exampleModalLabel"></h1>
                          </div>
                          <div class="modal-body">
                            <form method="post" enctype="multipart/form-data">
                              <div class="mb-3">
                                <label for="validationDefault01" class="col-form-label">Quantity :</label>
                                <input type="hidden" id="product_id" name="product_id" class="form-control">
                                <input type="hidden" id="price" name="price" class="form-control">
                                <input type="number" name="quantity" for="validationDefault01" class="form-control" maxlength="2" required>
                                
                              </div>
                       
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-danger">Add</button>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
                  </span>
                <td><?php echo $itemsb['specification']; ?></td>
                <td><?php echo $itemsb['stocks']; ?></td>
                <td><?php echo $itemsb['price']; ?></td>
                <td>
                  
                </td>
            </tr>
            <?php
            } 
            } 
            ?>
          </tbody>
          <script>
            const exampleModal2 = document.getElementById('exampleModal3')
            if (exampleModal2) {
              exampleModal2.addEventListener('show.bs.modal', event => {
                // Button that triggered the modal
                const button = event.relatedTarget
                // Extract info from data-bs-* attributes
                const recipient = button.getAttribute('data-bs-whatever')
                const recipient2 = button.getAttribute('data-bs-whatever2')
                const recipient3 = button.getAttribute('data-bs-whatever3')
                // If necessary, you could initiate an Ajax request here
                // and then do the updating in a callback.

                // Update the modal's content.
                const modalTitle = exampleModal2.querySelector('.modal-title')
                const modalBodyInput1 = document.getElementById('product_id')
                const modalBodyInput2 = document.getElementById('price')
            

                modalTitle.textContent = `${recipient}`
                modalBodyInput1.value = recipient2
                modalBodyInput2.value = recipient3
              })
            }
          </script>
        </table>
      </div>
    </main>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/feather-icons@4.28.0/dist/feather.min.js" integrity="sha384-uO3SXW5IuS1ZpFPKugNNWqTZRRglnUJK6UAZ/gxOX80nxEkN9NcGZTftn6RzhGWE" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</script><script src="dashboard.js"></script>
<script src="assets/js/jquery.js"></script>
  </body>
</html>