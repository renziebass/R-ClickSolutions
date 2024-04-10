<?php
include('user_session.php');
$sql0="SELECT
tb_products.id,
tb_supplier.name,
tb_products.supplier_id,
tb_products.price,
tb_products.disc,
tb_products.capital,
tb_products.product_brand,
tb_products.category,
tb_products.mc_brand,
tb_products.mc_model,
tb_products.price1,
tb_products.price2,
tb_products.price3
FROM tb_products
LEFT JOIN tb_supplier ON tb_products.supplier_id=tb_supplier.id
WHERE
CONCAT(tb_products.category,' ',tb_products.product_brand,' ',tb_products.mc_brand,' ',tb_products.mc_model)
LIKE '%".$_GET['search']."%'";
$result0=mysqli_query($db,$sql0);
$row0 = mysqli_fetch_assoc($result0);

$sql1a = "SELECT
tb_products.id,
CONCAT(tb_products.category,' ',tb_products.product_brand,' ',tb_products.mc_brand,' ',tb_products.mc_model)
AS specification
FROM tb_products";
$id = null;
$result1a=mysqli_query($db,$sql1a);

$sql4a = "SELECT * FROM `tb_products`";
$result4a=mysqli_query($db,$sql4a);

$sql1b="SELECT
tb_products.id,
tb_products.supplier_id,
tb_supplier.name,
tb_products.price,
tb_products.capital,
CONCAT(tb_products.price - tb_products.capital) AS profit,
tb_products.product_brand,
tb_products.category,
CONCAT(tb_products.category,' ',tb_products.product_brand,' ',tb_products.mc_brand,' ',tb_products.mc_model) AS specification,
CONCAT(tb_products.available,'/',tb_products.stocks) AS stocks
FROM tb_products
LEFT JOIN tb_supplier
ON tb_products.supplier_id=tb_supplier.id
WHERE CONCAT(tb_products.category,' ',tb_products.product_brand,' ',tb_products.mc_brand,' ',tb_products.mc_model)
LIKE '%".$_GET['search']."%'";
$result1b=mysqli_query($db,$sql1b);
$row1b = mysqli_fetch_assoc($result1b);

$id = $row1b['id'];

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
  if (empty($_POST["price1"])) {
    $price1_err = "* ";
  } else {
    $price1 = validateInput($_POST["price1"]);
  }
  if (empty($_POST["price2"])) {
    $price2_err = "* ";
  } else {
    $price2 = validateInput($_POST["price2"]);
  }
  if (empty($_POST["price3"])) {
    $price3_err = "* ";
  } else {
    $price3 = validateInput($_POST["price3"]);
  }
  
  if(!empty($_POST["pbrand"]) && !empty($_POST["price"]))
  {
  
      $sql4 = "UPDATE `tb_products` 
      SET 
      tb_products.supplier_id='$supplier',
      tb_products.product_brand='$pbrand',
      tb_products.category='$category',
      tb_products.mc_brand='$mcbrand',
      tb_products.mc_model='$mcmodel',
      tb_products.capital='$cap',
      tb_products.price='$price',
      tb_products.disc='$disc',
      tb_products.price1='$price1',
      tb_products.price2='$price2',
      tb_products.price3='$price3'
      WHERE tb_products.id='" .$id. "'";
      $update = mysqli_query($db, $sql4);
        
      echo '<script>alert("Product Updated Successfuly!")</script>';
      header("Refresh:0");

  }
}

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
.autocomplete-items {
      position: absolute;
      border: 1px solid #d4d4d4;
      border-bottom: none;
      border-top: none;
      z-index: 99;
      /*position the autocomplete items to be the same width as the container:*/
      top: 100%;
      left: 0;
      right: 0;
    }
    .autocomplete {
      position: relative;
      display: inline-block;
    }
    .autocomplete-items div {
      padding: 10px;
      cursor: pointer;
      background-color: #fff; 
      border-bottom: 1px solid #d4d4d4; 
    }
    .autocomplete-items div:hover {
      background-color: #e9e9e9; 
    }
    .autocomplete-active {
      background-color: DodgerBlue !important; 
      color: #ffffff; 
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
    <h6 class="text-center mb-3 mt-5">Update Product: <?php echo $row1b['id'];?></h6>

    <div class="col">
        <form method="get" action="<?php echo $_SERVER['PHP_SELF'];?>" autocomplete=off>
        <div class="mb-3">
            <div class="input-group shadow">
            <div class="autocomplete col">
            <input id="search" onkeyup="this.value = this.value.toUpperCase();" name="search" class="form-control" list="datalistOptions" id="exampleDataList" placeholder="Type to search product...">
            </div>
              <script>
                window.onload = init;
                function init(){
                document.getElementById("search").focus();
                }
              </script>
             
              <button class="btn btn-secondary" type="submit" id="button-addon2"><span data-feather="search" class="align-text-end"></button>
            </div>
        </div>
      </form>
        </div>
        <div class="mb-3">
        <div class="row ">
          
          <div class="col text-center">
            <div class="card-body p-2">
                <h6 class="fw-normal mt-0"><?php echo $row1b['name'];?></h6>
                <h3 class=""><?php echo $row1b['specification'];?></h3>
                <p class="mb-0 text-muted">
                <p class="mb-0 text-muted">
                <span class="text-primary fw-bold"><?php echo $row1b['stocks'];?></span>
                <span class="text-nowrap me-2">Stocks</span> 
                </p>
                </p>
            </div>
          </div>
          <div class="col text-center">
            <div class="card-body p-2">
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
                    <option selected value="<?php echo $row0['supplier_id'];?>"><?php echo $row0['name'];?></option>
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
          <div class="row mb-3">
            <div class="col-md">
              <div class="form-floating shadow">
                <input name="price1" type="number" value="<?php echo $row0['price1'];?>" class="form-control" aria-label="Text input with dropdown button" placeholder="CAP" required>
                <label for="floatingInputGrid">PRICE 1</label>
              </div>
            </div>
            <div class="col-md">
              <div class="form-floating shadow">
                <input name="price2" type="decimal" value="<?php echo $row0['price2'];?>" class="form-control" aria-label="Text input with dropdown button" placeholder="SRP" required>
                <label for="floatingInputGrid">PRICE 2</label>
              </div>
            </div>
            <div class="col-md">
              <div class="form-floating shadow">
                <input name="price3" type="decimal" value="<?php echo $row0['price3'];?>" class="form-control" aria-label="Text input with dropdown button" placeholder="SRP" required>
                <label for="floatingInputGrid">PRICE 3</label>
              </div>
            </div>
          </div>     
          <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <button class="btn btn-success" type="submit"><span data-feather="save" class="align-text-end"></button>
          </div>
        </form>
      </div>
      <div class="table" id="page">
      
    </main>
  </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@4/dark.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<script>
  function init(){
    document.getElementById("search").focus();
    }
  function autocomplete(inp, arr) {
            var currentFocus;
            inp.addEventListener("input", function(e) {
                var a, b, i, val = this.value;
                closeAllLists();
                if (!val) { return false;}
                currentFocus = -1;
                a = document.createElement("DIV");
                a.setAttribute("id", this.id + "autocomplete-list");
                a.setAttribute("class", "autocomplete-items");
                this.parentNode.appendChild(a);
                for (i = 0; i < arr.length; i++) {
                  if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
                    b = document.createElement("DIV");
                    b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
                    b.innerHTML += arr[i].substr(val.length);
                    b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
                    b.addEventListener("click", function(e) {
                        inp.value = this.getElementsByTagName("input")[0].value;
                        closeAllLists();
                    });
                    a.appendChild(b);
                  }
                }
            });
            inp.addEventListener("keydown", function(e) {
                var x = document.getElementById(this.id + "autocomplete-list");
                if (x) x = x.getElementsByTagName("div");
                if (e.keyCode == 40) {
                  currentFocus++;
                  addActive(x);
                } else if (e.keyCode == 38) {
                  currentFocus--;
                  addActive(x);
                } else if (e.keyCode == 13) {
                  
                  if (currentFocus > -1) {
                    if (x) x[currentFocus].click();
                  }
                  if (inp.value == "") {
                    e.preventDefault();
                  }
                }
            });
            function addActive(x) {
              if (!x) return false;
              removeActive(x);
              if (currentFocus >= x.length) currentFocus = 0;
              if (currentFocus < 0) currentFocus = (x.length - 1);
              x[currentFocus].classList.add("autocomplete-active");
            }
            function removeActive(x) {
              for (var i = 0; i < x.length; i++) {
                x[i].classList.remove("autocomplete-active");
              }
            }
            function closeAllLists(elmnt) {
              var x = document.getElementsByClassName("autocomplete-items");
              for (var i = 0; i < x.length; i++) {
                if (elmnt != x[i] && elmnt != inp) {
                  x[i].parentNode.removeChild(x[i]);
                }
              }
            }
            document.addEventListener("click", function (e) {
                closeAllLists(e.target);
            });
          }
          var specification = [
            <?php while($row1a = mysqli_fetch_array($result1a)):;?>
            "<?php echo $row1a['specification'];?>",
            <?php endwhile; ?>
          ];
          var pb = [
            <?php while($row4a = mysqli_fetch_array($result4a)):;?>
            "<?php echo $row4a['product_brand'];?>",
            <?php endwhile; ?>
          ];

          autocomplete(document.getElementById("search"), specification.concat(pb));
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/feather-icons@4.28.0/dist/feather.min.js" integrity="sha384-uO3SXW5IuS1ZpFPKugNNWqTZRRglnUJK6UAZ/gxOX80nxEkN9NcGZTftn6RzhGWE" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</script><script src="dashboard.js"></script>

  </body>
</html>
