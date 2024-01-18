<?php
include('user_session.php');
$sql0="SELECT
tb_products.id,
tb_supplier.name,
tb_products.price,
tb_products.disc,
tb_products.capital,
tb_products.product_brand,
tb_products.category,
tb_products.mc_brand,
tb_products.mc_model
FROM tb_products
LEFT JOIN tb_supplier ON tb_products.supplier_id=tb_supplier.id
WHERE tb_products.id='" .$_GET['id']. "'";
$result0=mysqli_query($db,$sql0);
$row0 = mysqli_fetch_assoc($result0);

function validateInput($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (empty($_POST["supplier"])) {
    $supplier_err = "* ";
  } else {
    $supplier = validateInput($_POST["supplier"]);
  }
  if (empty($_POST["category"])) {
    $category_err = "* ";
  } else {
    $category = validateInput($_POST["category"]);
  }
  if (empty($_POST["mcbrand"])) {
    $mcbrand_err = "* ";
  } else {
    $mcbrand = validateInput($_POST["mcbrand"]);
  }
  if (empty($_POST["mcmodel"])) {
    $mcmodel_err = "* ";
  } else {
    $mcmodel = validateInput($_POST["mcmodel"]);
  }
  if (empty($_POST["pbrand"])) {
    $pbrand_err = "* ";
  } else {
    $pbrand = validateInput($_POST["pbrand"]);
  }

  if (empty($_POST["price"])) {
    $price_err = "* ";
  } else {
    $price = validateInput($_POST["price"]);
  }
  if (empty($_POST["disc"])) {
    $disc_err = "* ";
  } else {
    $disc = validateInput($_POST["disc"]);
  }
  if (empty($_POST["cap"])) {
    $cap_err = "* ";
  } else {
    $cap = validateInput($_POST["cap"]);
  }
  
  if(!empty($_POST["pbrand"]) && !empty($_POST["price"]))
  {
    try {

      $sql4 = "UPDATE `tb_products` 
      SET 
      tb_products.supplier_id='$supplier',
      tb_products.product_brand='$pbrand',
      tb_products.category='$category',
      tb_products.mc_brand='$mcbrand',
      tb_products.mc_model='$mcmodel',
      tb_products.capital='$cap',
      tb_products.price='$price',
      tb_products.disc='$disc'
      WHERE tb_products.id='" .$_GET['id']. "'";
      $update = mysqli_query($db, $sql4);
        
      echo '<script>alert("Product Updated Successfuly!")</script>';
      header("Refresh:0");


    }
    catch(PDOException $e)
      {
        echo $sql1 . "<br>" . $e->getMessage();
      }
    $db=null;
  }
}

$sql1a = "SELECT
tb_products.id,
CONCAT(tb_products.category,' ',tb_products.product_brand,' ',tb_products.mc_brand,' ',tb_products.mc_model)
AS specification
FROM tb_products";
$id = null;
$result1a=mysqli_query($db,$sql1a);
if ($_SERVER["REQUEST_METHOD"] == "GET") {
  if (!empty($_GET["id"])) {
    $id = $_GET["id"];
  }
}
$sql1b="SELECT
tb_products.id,
tb_products.supplier_id,
tb_products.price,
tb_products.capital,
CONCAT(tb_products.price - tb_products.capital) AS profit,
tb_products.product_brand,
tb_products.category,
CONCAT(tb_products.category,' ',tb_products.product_brand,' ',tb_products.mc_brand,' ',tb_products.mc_model) AS specification,
CONCAT(tb_products.available,'/',tb_products.stocks) AS stocks
FROM tb_products
WHERE tb_products.id='" .$_GET['id']. "'";
$result1b=mysqli_query($db,$sql1b);
$row1b = mysqli_fetch_assoc($result1b);

$sql1 = "SELECT * FROM tb_product_category";
$result1=mysqli_query($db,$sql1);

$sql2 = "SELECT * FROM tb_mc_brand ORDER BY tb_mc_brand.brand ASC";
$result2=mysqli_query($db,$sql2);

$sql3 = "SELECT * FROM tb_mc_model ORDER BY tb_mc_model.model ASC";
$result3=mysqli_query($db,$sql3);

$sql4 = "SELECT * FROM `tb_supplier`";
$result4=mysqli_query($db,$sql4);
?>
<!doctype html>
<html lang="en" data-bs-theme="auto">
  <head><script src="../assets/js/color-modes.js"></script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.111.3">
    <title>UPDATE PRODUCT</title>
 
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
            <a class="nav-link" aria-current="page" href="admin_dashboard.php">
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
            <a class="nav-link active" href="admin_add_products.php">
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

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <h6 class="text-center mb-3 mt-5">Update Product</h6>

    <div class="col">
        <form method="get" action="<?php echo $_SERVER['PHP_SELF'];?>">
        <div class="mb-3">
            <div class="input-group">
              <input id="search" onkeyup="this.value = this.value.toUpperCase();" name="id" class="form-control" list="datalistOptions" id="exampleDataList" value="" placeholder="<?php echo $row0['id'];?>">
              <script>
                window.onload = init;
                function init(){
                document.getElementById("search").focus();
                }
              </script>
              <datalist id="datalistOptions">
                <?php while($row1a = mysqli_fetch_array($result1a)):;?>
                <option value="<?php echo $row1a['id'];?>"><?php echo $row1a['specification'];?></option>
                <?php endwhile; ?>
              </datalist>
              <button class="btn btn-secondary" type="submit" id="button-addon2">SEARCH <span data-feather="search" class="align-text-end"></button>
            </div>
        </div>
      </form>
        </div>
        <div class="mb-3">
        <div class="row border rounded shadow m-1">
          
          <div class="col m-1">
            <div class="card-body p-2">
              <div class="float-end">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-receipt-cutoff" viewBox="0 0 16 16">
                  <path d="M3 4.5a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5M11.5 4a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1z"/>
                  <path d="M2.354.646a.5.5 0 0 0-.801.13l-.5 1A.5.5 0 0 0 1 2v13H.5a.5.5 0 0 0 0 1h15a.5.5 0 0 0 0-1H15V2a.5.5 0 0 0-.053-.224l-.5-1a.5.5 0 0 0-.8-.13L13 1.293l-.646-.647a.5.5 0 0 0-.708 0L11 1.293l-.646-.647a.5.5 0 0 0-.708 0L9 1.293 8.354.646a.5.5 0 0 0-.708 0L7 1.293 6.354.646a.5.5 0 0 0-.708 0L5 1.293 4.354.646a.5.5 0 0 0-.708 0L3 1.293zm-.217 1.198.51.51a.5.5 0 0 0 .707 0L4 1.707l.646.647a.5.5 0 0 0 .708 0L6 1.707l.646.647a.5.5 0 0 0 .708 0L8 1.707l.646.647a.5.5 0 0 0 .708 0L10 1.707l.646.647a.5.5 0 0 0 .708 0L12 1.707l.646.647a.5.5 0 0 0 .708 0l.509-.51.137.274V15H2V2.118l.137-.274z"/>
                </svg>
              </div>
                <h6 class="fw-normal mt-0"><?php echo $row1b['supplier_id'];?></h6>
                <h3 class=""><?php echo $row1b['specification'];?></h3>
                <p class="mb-0 text-muted">
                <p class="mb-0 text-muted">
                <span class="text-primary fw-bold"><?php echo $row1b['stocks'];?></span>
                <span class="text-nowrap me-2">Stocks</span> 
                </p>
                </p>
            </div>
          </div>
          <div class="col m-1">
            <div class="card-body p-2">
              <div class="float-end">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-receipt-cutoff" viewBox="0 0 16 16">
                  <path d="M3 4.5a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5M11.5 4a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h1a.5.5 0 0 0 0-1z"/>
                  <path d="M2.354.646a.5.5 0 0 0-.801.13l-.5 1A.5.5 0 0 0 1 2v13H.5a.5.5 0 0 0 0 1h15a.5.5 0 0 0 0-1H15V2a.5.5 0 0 0-.053-.224l-.5-1a.5.5 0 0 0-.8-.13L13 1.293l-.646-.647a.5.5 0 0 0-.708 0L11 1.293l-.646-.647a.5.5 0 0 0-.708 0L9 1.293 8.354.646a.5.5 0 0 0-.708 0L7 1.293 6.354.646a.5.5 0 0 0-.708 0L5 1.293 4.354.646a.5.5 0 0 0-.708 0L3 1.293zm-.217 1.198.51.51a.5.5 0 0 0 .707 0L4 1.707l.646.647a.5.5 0 0 0 .708 0L6 1.707l.646.647a.5.5 0 0 0 .708 0L8 1.707l.646.647a.5.5 0 0 0 .708 0L10 1.707l.646.647a.5.5 0 0 0 .708 0L12 1.707l.646.647a.5.5 0 0 0 .708 0l.509-.51.137.274V15H2V2.118l.137-.274z"/>
                </svg>
              </div>
                <h6 class="fw-normal mt-0" title="Number of Customers">Price</h6>
                <h3 class=""><?php echo $row1b['price'];?></h3>
                <p class="mb-0 text-muted">
                <p class="mb-0 text-muted">
                <span class="text-danger fw-bold"><?php echo $row1b['capital'];?></span>
                <span class="text-nowrap me-2">Capital</span>  
                <span class="text-primary fw-bold"><?php echo $row1b['profit'];?></span>
                <span class="text-nowrap">Profit</span>  
                </p>
                </p>
            </div>
          </div>

        </div>
      </div>
      <div class="align-items-center ">
      <form method="post" action="" enctype="multipart/form-data">
          <div class="row mb-3">
            <div class="col-md">
              <div class="form-floating shadow">
                  <select name="supplier" class="form-select" id="inputGroupSelect04" aria-label="Example select with button addon">
                    <option selected value="<?php echo $row0['name'];?>"><?php echo $row0['name'];?></option>
                    <?php while($row4 = mysqli_fetch_array($result4)):;?> 
                    <option class="dropdown-item" value="<?php echo $row4['id'];?>">
                    <?php echo $row4['name'];?></option>
                    <?php endwhile; ?>
                  </select>
                <label for="inputGroupSelect04">SUPPLIER</label>
              </div>
            </div>
            <div class="col-md">
              <div class="form-floating shadow">
                  <select name="category" class="form-select" id="inputGroupSelect05" aria-label="Example select with button addon">
                    <option selected value="<?php echo $row0['category'];?>"><?php echo $row0['category'];?></option>
                    <?php while($row1 = mysqli_fetch_array($result1)):;?> 
                    <option class="dropdown-item" value="<?php echo $row1['category'];?>">
                    <?php echo $row1['category'];?></option>
                    <?php endwhile; ?>
                  </select>
                <label for="inputGroupSelect05">PRODUCT CATEGORY</label>
              </div>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md">
              <div class="form-floating shadow">
                <select name="mcbrand" class="form-select" id="inputGroupSelect06" aria-label="Example select with button addon">
                  <option selected value="<?php echo $row0['mc_brand'];?>"><?php echo $row0['mc_brand'];?></option>
                  <?php while($row2 = mysqli_fetch_array($result2)):;?> 
                  <option class="dropdown-item" value="<?php echo $row2['brand'];?>">
                  <?php echo $row2['brand'];?></option>
                  <?php endwhile; ?>
                </select>
                <label for="inputGroupSelect06">BRAND</label>
              </div>
            </div>
            <div class="col-md">
              <div class="form-floating shadow">
                <select name="mcmodel" class="form-select" id="inputGroupSelect04" aria-label="Example select with button addon">
                  <option selected value="<?php echo $row0['mc_model'];?>"><?php echo $row0['mc_model'];?></option>
                  <?php while($row3 = mysqli_fetch_array($result3)):;?> 
                  <option class="dropdown-item" value="<?php echo $row3['model'];?>">
                  <?php echo $row3['model'];?></option>
                  <?php endwhile; ?>
                </select>
                <label for="inputGroupSelect07">MODEL</label>
              </div>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md">
              <div class="form-floating shadow">
                <input class="form-control" name="pbrand" type="text" onkeyup="this.value = this.value.toUpperCase();" value="<?php echo $row0['product_brand'];?>" class="form-control" aria-label="Text input with dropdown button" placeholder="Product Brand" required>
                <label for="floatingInputGrid">PRODUCT BRAND & DESCRIPTION</label>
              </div>
            </div>
            <div class="col-md">
              <div class="form-floating shadow">
                <input class="form-control" name="disc" type="number" onkeyup="this.value = this.value.toUpperCase();" value="<?php echo $row0['disc'];?>" class="form-control" aria-label="Text input with dropdown button" placeholder="Product Brand" required>
                <label for="floatingInputGrid">DISCOUNT</label>
              </div>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md">
              <div class="form-floating shadow">
                <input name="cap" type="number" value="<?php echo $row0['capital'];?>" class="form-control" aria-label="Text input with dropdown button" placeholder="CAP" required>
                <label for="floatingInputGrid">CAPITAL</label>
              </div>
            </div>
            <div class="col-md">
              <div class="form-floating shadow">
                <input name="price" type="decimal" value="<?php echo $row0['price'];?>" class="form-control" aria-label="Text input with dropdown button" placeholder="SRP" required>
                <label for="floatingInputGrid">SRP</label>
              </div>
            </div>
          </div>      
          <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <button class="btn btn-success" type="submit">UPDATE <span data-feather="upload-cloud" class="align-text-end"></button>
          </div>
        </form>
      </div>
      <div class="table" id="page">
      
    </main>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/feather-icons@4.28.0/dist/feather.min.js" integrity="sha384-uO3SXW5IuS1ZpFPKugNNWqTZRRglnUJK6UAZ/gxOX80nxEkN9NcGZTftn6RzhGWE" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</script><script src="dashboard.js"></script>

  </body>
</html>
