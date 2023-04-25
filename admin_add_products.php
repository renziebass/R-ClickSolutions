<?php
include('user_session.php');
if(!empty($_GET['xid'])) {
  $sql7="DELETE FROM tb_products WHERE tb_products.id='" .$_GET['xid']. "'";

  if (($db->query($sql7)) === TRUE) {
    echo "Product Deleted";
    header("Location: admin_add_products.php");
  } else {
    echo "Error updating record: " . $db->error;
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
  if (empty($_POST["qty"])) {
    $qty_err = "* ";
  } else {
    $qty = validateInput($_POST["qty"]);
  }
  if (empty($_POST["price"])) {
    $price_err = "* ";
  } else {
    $price = validateInput($_POST["price"]);
  }
  
  if(!empty($_POST["pbrand"]) && !empty($_POST["qty"] && !empty($_POST["price"])))
  {
    try {
      $sql5check = "SELECT * FROM tb_products
      WHERE tb_products.product_brand='$pbrand'
      AND tb_products.category='$category'
      AND tb_products.mc_model='$mcmodel'
      AND tb_products.supplier_id='$supplier'";
      $CategoryCheck5 = mysqli_query($db, $sql5check);
      $result5 = mysqli_fetch_assoc($CategoryCheck5);
      if(empty($result5)) {
        $sql4 = "INSERT INTO `tb_products` (`id`, `supplier_id`, `product_brand`, `category`, `mc_brand`, `mc_model`, `stocks`, `available`, `price`,`date`)
        VALUES ('".date("YmdHis")."', '$supplier', '$pbrand', '$category', '$mcbrand', '$mcmodel', '$qty', '$qty', '$price','".date("Y-m-d H:i:s")."')";
        $AddCategory = mysqli_query($db, $sql4);
        
        header("Location: admin_add_products.php");
      } else {
        echo '<script>alert("Product Already Exist!")</script>';
        header("Refresh:0");
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
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ADD PRODUCT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link href="sidebars.css" rel="stylesheet">
</head>
  <body class="" onload="navbar();">
  <script type="text/javascript">
      function navbar(){
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function(){
          document.getElementById("navbar").innerHTML = this.responseText;
        }
        xhttp.open("GET", "admin_navbar.php");
        xhttp.send();
      }
      setInterval(function(){
        navbar();
      }, 10000);
    </script>
  <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
  <symbol id="bootstrap" viewBox="0 0 118 94">
    <path fill-rule="evenodd" clip-rule="evenodd" d="M24.509 0c-6.733 0-11.715 5.893-11.492 12.284.214 6.14-.064 14.092-2.066 20.577C8.943 39.365 5.547 43.485 0 44.014v5.972c5.547.529 8.943 4.649 10.951 11.153 2.002 6.485 2.28 14.437 2.066 20.577C12.794 88.106 17.776 94 24.51 94H93.5c6.733 0 11.714-5.893 11.491-12.284-.214-6.14.064-14.092 2.066-20.577 2.009-6.504 5.396-10.624 10.943-11.153v-5.972c-5.547-.529-8.934-4.649-10.943-11.153-2.002-6.484-2.28-14.437-2.066-20.577C105.214 5.894 100.233 0 93.5 0H24.508zM80 57.863C80 66.663 73.436 72 62.543 72H44a2 2 0 01-2-2V24a2 2 0 012-2h18.437c9.083 0 15.044 4.92 15.044 12.474 0 5.302-4.01 10.049-9.119 10.88v.277C75.317 46.394 80 51.21 80 57.863zM60.521 28.34H49.948v14.934h8.905c6.884 0 10.68-2.772 10.68-7.727 0-4.643-3.264-7.207-9.012-7.207zM49.948 49.2v16.458H60.91c7.167 0 10.964-2.876 10.964-8.281 0-5.406-3.903-8.178-11.425-8.178H49.948z"></path>
  </symbol>
  <symbol id="home" viewBox="0 0 16 16">
    <path d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5a.5.5 0 0 0 .5-.5v-4h2v4a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.354 1.146zM2.5 14V7.707l5.5-5.5 5.5 5.5V14H10v-4a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v4H2.5z"/>
  </symbol>
  <symbol id="speedometer2" viewBox="0 0 16 16">
    <path d="M8 4a.5.5 0 0 1 .5.5V6a.5.5 0 0 1-1 0V4.5A.5.5 0 0 1 8 4zM3.732 5.732a.5.5 0 0 1 .707 0l.915.914a.5.5 0 1 1-.708.708l-.914-.915a.5.5 0 0 1 0-.707zM2 10a.5.5 0 0 1 .5-.5h1.586a.5.5 0 0 1 0 1H2.5A.5.5 0 0 1 2 10zm9.5 0a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 0 1H12a.5.5 0 0 1-.5-.5zm.754-4.246a.389.389 0 0 0-.527-.02L7.547 9.31a.91.91 0 1 0 1.302 1.258l3.434-4.297a.389.389 0 0 0-.029-.518z"/>
    <path fill-rule="evenodd" d="M0 10a8 8 0 1 1 15.547 2.661c-.442 1.253-1.845 1.602-2.932 1.25C11.309 13.488 9.475 13 8 13c-1.474 0-3.31.488-4.615.911-1.087.352-2.49.003-2.932-1.25A7.988 7.988 0 0 1 0 10zm8-7a7 7 0 0 0-6.603 9.329c.203.575.923.876 1.68.63C4.397 12.533 6.358 12 8 12s3.604.532 4.923.96c.757.245 1.477-.056 1.68-.631A7 7 0 0 0 8 3z"/>
  </symbol>
  <symbol id="table" viewBox="0 0 16 16">
    <path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zm15 2h-4v3h4V4zm0 4h-4v3h4V8zm0 4h-4v3h3a1 1 0 0 0 1-1v-2zm-5 3v-3H6v3h4zm-5 0v-3H1v2a1 1 0 0 0 1 1h3zm-4-4h4V8H1v3zm0-4h4V4H1v3zm5-3v3h4V4H6zm4 4H6v3h4V8z"/>
  </symbol>
  <symbol id="people-circle" viewBox="0 0 16 16">
    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
    <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
  </symbol>
  <symbol id="grid" viewBox="0 0 16 16">
    <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z"/>
  </symbol>
</svg>
  <main class="d-flex" style="height: 800px;">
  <div id="navbar" class="d-flex flex-column flex-shrink-0 p-3 text-bg-secondary" style="width: 280px;">
      
  </div>

  <div class="b-example-divider b-example-vr p-3 flex-fill">
  <div id="content">
    <div class="container-fluid mt-2">
    <div class="col-md-12">
        <div class="card shadow border-start-primary" style="height:700px;">
            <div class="card-body">
            <div class="container text-center">
                <div class="row align-items-start">
                    <div class="col">
                    <form method="post" action="" enctype="multipart/form-data">
                      <div class="input-group">
                          <select name="supplier" class="form-select" id="inputGroupSelect04" aria-label="Example select with button addon">
                            <?php while($row4 = mysqli_fetch_array($result4)):;?> 
                            <option class="dropdown-item" value="<?php echo $row4['id'];?>">
                            <?php echo $row4['name'];?></option>
                            <?php endwhile; ?>
                          </select>
                          <select name="category" class="form-select" id="inputGroupSelect04" aria-label="Example select with button addon">
                            <?php while($row1 = mysqli_fetch_array($result1)):;?> 
                            <option class="dropdown-item" value="<?php echo $row1['category'];?>">
                            <?php echo $row1['category'];?></option>
                            <?php endwhile; ?>
                          </select>
                          <select name="mcbrand" class="form-select" id="inputGroupSelect04" aria-label="Example select with button addon">
                            <?php while($row2 = mysqli_fetch_array($result2)):;?> 
                            <option class="dropdown-item" value="<?php echo $row2['brand'];?>">
                            <?php echo $row2['brand'];?></option>
                            <?php endwhile; ?>
                          </select>
                          <select name="mcmodel" class="form-select" id="inputGroupSelect04" aria-label="Example select with button addon">
                            <?php while($row3 = mysqli_fetch_array($result3)):;?> 
                            <option class="dropdown-item" value="<?php echo $row3['model'];?>">
                            <?php echo $row3['model'];?></option>
                            <?php endwhile; ?>
                          </select>
                          <input class="col-4" name="pbrand" type="text" onkeyup="this.value = this.value.toUpperCase();" class="form-control" aria-label="Text input with dropdown button" placeholder="Product Brand">
                          <input name="qty" type="number" class="form-control" aria-label="Text input with dropdown button" placeholder="QTY">
                          <input name="price" type="decimal" class="form-control" aria-label="Text input with dropdown button" placeholder="SRP">
                        <button class="btn btn-outline-secondary" type="submit">Add</button>
                      </div>
                    </form>
                    </div>
                </div>
                <div class="row align-items-start overflow-auto mt-3" id="page" style="height: 620px;">
                    <div class="col-12" id="">
                        <div class="row">
                            <div class="col-12">
                                <div class="row">
                                  <div class="col"></div>
                                  <div class="col-2 text-muted">
                                        PRODUCT ID
                                    </div>
                                    <div class="col text-muted">
                                        SUPPLIER
                                    </div>
                                    <div class="col-4 text-muted">
                                        SPECIFICATION
                                    </div>
                                    <div class="col-3 text-muted">
                                        PRODUCT BRAND
                                    </div>
                                    <div class="col text-muted">
                                        QTY
                                    </div>
                                    <div class="col text-muted">
                                        SRP
                                    </div>
                                </div>
                                <?php
                  $sql="SELECT
                  tb_products.id,
                  tb_products.supplier_id,
                  CONCAT(tb_products.category,' ',tb_products.mc_brand,' ',tb_products.mc_model) AS specification,
                  tb_products.product_brand,
                  tb_products.stocks,
                  tb_products.price
                  FROM tb_products
                  ORDER BY tb_products.date DESC";
                                                      
                  $result = mysqli_query($db,$sql);
      
                  if (mysqli_num_rows($result) > 0) 
                  {
                  foreach($result as $items)
                  {
                ?>
                                <div class="row">
                                    <div class="col">
                                    <svg onclick="location.href='admin_add_products.php?xid=<?php echo $items['id'];?>'" class="text-danger" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-square-fill" viewBox="0 0 16 16">
                                        <path d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm3.354 4.646L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 1 1 .708-.708z"></svg>
                                    </div>
                                    <div class="col-2">
                                    <a class="" onclick="location.href='admin_product.php?id=<?php echo $items['id'];?>'"><?php echo $items['id'];?></a>
                                    </div>
                                    <div class="col">
                                    <?php echo $items['supplier_id']; ?>
                                    </div>
                                    <div class="col-4">
                                    <?php echo $items['specification']; ?>
                                    </div>
                                    <div class="col-3">
                                    <?php echo $items['product_brand']; ?>
                                    </div>
                                    <div class="col">
                                    <?php echo $items['stocks']; ?>
                                    </div>
                                    <div class="col">
                                    <?php echo $items['price']; ?>
                                    </div>
                                </div>
                                <?php
                                } 
                                } 
                            ?>
                            </div>
                            
                        </div>
                    </div>
                </div>
                </div>
            </div>
    </div>
  </div>
</main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
  </body>
</html>