<?php
include('user_session.php');
$sql3 = "SELECT * FROM `tb_mc_brand`";
$result3=mysqli_query($db,$sql3);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (empty($_POST["category"])) {
    $category_err = "* ";
  } else {
    $category = validateInput($_POST["category"]);
  }
  if (empty($_POST["mb"])) {
    $mb_err = "* ";
  } else {
    $mb = validateInput($_POST["mb"]);
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
  
}
function validateInput($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}
if(!empty($_POST["category"]))
  {
    try {
      $sql4check = "SELECT tb_product_category.category
      FROM tb_product_category
      WHERE tb_product_category.category='$category'";
      $CategoryCheck4 = mysqli_query($db, $sql4check);
      $result4 = mysqli_fetch_assoc($CategoryCheck4);
      if(empty($result4)) {
        $sql4 = "INSERT INTO tb_product_category (category) VALUES ('$category');";
        $AddCategory = mysqli_query($db, $sql4);
        
        header("Location: admin_add_category.php");
      } else {
        echo '<script>alert("Category Already Exist!")</script>';
        header("Refresh:0");
      }

    }
    catch(PDOException $e)
      {
        echo $sql1 . "<br>" . $e->getMessage();
      }
    $db=null;
}
if(!empty($_POST["mb"]))
  {
    try {
      $sql5check = "SELECT tb_mc_brand.brand
      FROM tb_mc_brand
      WHERE tb_mc_brand.brand='$mb'";
      $Check5 = mysqli_query($db, $sql5check);
      $result5 = mysqli_fetch_assoc($Check5);
      if(empty($result5)) {
        $sql5 = "INSERT INTO tb_mc_brand (brand) VALUES ('$mb');";
        $Addmb = mysqli_query($db, $sql5);
        header("Location: admin_add_category.php");
      } else {
        echo '<script>alert("Motorcycle Brand Already Exist!")</script>';
        header("Refresh:0");
      }

    }
    catch(PDOException $e)
      {
        echo $sql1 . "<br>" . $e->getMessage();
      }
    $db=null;
}
if(!empty($_POST["mcbrand"]) && !empty($_POST["mcmodel"]))
  {
    try {
      $sql6check = "SELECT 
      tb_mc_model.brand,
      tb_mc_model.model
      FROM tb_mc_model
      WHERE tb_mc_model.brand='$mcbrand'
      AND tb_mc_model.model='$mcmodel'";
      $Check6 = mysqli_query($db, $sql6check);
      $result6 = mysqli_fetch_assoc($Check6);
      if(empty($result6)) {
        $sql6 = "INSERT INTO tb_mc_model (brand,model) VALUES ('$mcbrand','$mcmodel')";
        $Addmc = mysqli_query($db, $sql6);
        header("Location: admin_add_category.php");
      } else {
        echo '<script>alert("Motorcycle Model Already Exist!")</script>';
        header("Refresh:0");
      }

    }
    catch(PDOException $e)
      {
        echo $sql1 . "<br>" . $e->getMessage();
      }
    $db=null;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  
  if (empty($_POST["id"])) {
    $id_err = "* ";
  } else {
    $id = validateInput($_POST["id"]);
  }
  if (empty($_POST["name"])) {
    $name_err = "* ";
  } else {
    $name = validateInput($_POST["name"]);
  }
  if (empty($_POST["address"])) {
    $address_err = "* ";
  } else {
    $address = validateInput($_POST["address"]);
  }
  if (empty($_POST["notes"])) {
    $notes_err = "* ";
  } else {
    $notes = validateInput($_POST["notes"]);
  }
  if (empty($_POST["number"])) {
    $number_err = "* ";
  } else {
    $number = validateInput($_POST["number"]);
  }
  
  if(!empty($_POST["id"]) && !empty($_POST["name"] ) && !empty($_POST["address"]) && !empty($_POST["notes"]) && !empty($_POST["number"]))
  {
    try {

        $sql2 = "INSERT INTO `tb_supplier` (`id`, `name`, `address`, `notes`, `mobile`)
        VALUES ('$id','$name','$address','$notes','$number')";
        $saveSupplier = mysqli_query($db, $sql2);
        
        header("Location: admin_add_category.php");
      
    }
    catch(PDOException $e)
      {
        echo $sql2 . "<br>" . $e->getMessage();
      }
    $db=null;
}
}
if(!empty($_GET['xcategory'])) {
  $sql7="DELETE FROM tb_product_category WHERE tb_product_category.category='" .$_GET['xcategory']. "'";

  if (($db->query($sql7)) === TRUE) {
    echo "Category Deleted";
    header("Location: admin_add_category.php");
  } else {
    echo "Error updating record: " . $db->error;
  }
}
if(!empty($_GET['xbrand'])) {
  $sql8="DELETE FROM tb_mc_brand WHERE tb_mc_brand.brand='" .$_GET['xbrand']. "'";

  if (($db->query($sql8)) === TRUE) {
    echo "Brand Deleted";
    header("Location: admin_add_category.php");
  } else {
    echo "Error updating record: " . $db->error;
  }
}
if(!empty($_GET['xmcbrand']) && !empty($_GET['xmcmodel'])) {
  $sql9="DELETE FROM tb_mc_model WHERE tb_mc_model.brand='" .$_GET['xmcbrand']. "' AND tb_mc_model.model='" .$_GET['xmcmodel']. "'";

  if (($db->query($sql9)) === TRUE) {
    echo "Model Deleted";
    header("Location: admin_add_category.php");
  } else {
    echo "Error updating record: " . $db->error;
  }
}
if(!empty($_GET['xid'])) {
  $sql7="DELETE FROM tb_supplier WHERE tb_supplier.id='" .$_GET['xid']. "'";

  if (($db->query($sql7)) === TRUE) {
    echo "Supplier Deleted";
    header("Location: admin_add_category.php");
  } else {
    echo "Error updating record: " . $db->error;
  }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MANAGE CATEGORY & SUPPLIER</title>
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
                  <div class="row align-items-start" style="height:300px;">
                      <div class="col-4">
                      <form method="post" action="" enctype="multipart/form-data">
                          <div class="input-group input-group-sm mb-3">
                            <input type="text" name="category" class="form-control" placeholder="New Product Category" aria-label="Recipient's username" aria-describedby="button-addon2">
                            <button class="btn btn-outline-secondary" type="submit" id="button-addon2">Add</button>
                          </div>
                      </form>
                          <div class="container overflow-auto" style="height:250px;">
                          <?php
                            $sql="SELECT
                            tb_product_category.category
                            FROM tb_product_category  
                            ORDER BY tb_product_category.category ASC";
                                                                
                            $result = mysqli_query($db,$sql);
                
                            if (mysqli_num_rows($result) > 0) 
                            {
                            foreach($result as $items)
                            {
                          ?>
                          <div class="row">
                              <div class="col text-start">
                              <svg onclick="location.href='admin_add_category.php?xcategory=<?php echo $items['category'];?>'" class="text-danger" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-square-fill" viewBox="0 0 16 16">
                              <path d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm3.354 4.646L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 1 1 .708-.708z"></svg>
                              <?php echo $items['category']; ?>
                              </div>
                          </div>
                          <?php
                            } 
                          } 
                          ?>
                          </div>
                      </div>
                      <div class="col-4">
                      <form method="post" action="" enctype="multipart/form-data">
                          <div class="input-group input-group-sm mb-3">
                            <input type="text" name="mb" class="form-control" placeholder="New Motorcycle Brand" aria-label="Recipient's username" aria-describedby="button-addon2">
                            <button class="btn btn-outline-secondary" type="submit" id="button-addon2">Add</button>
                          </div>
                      </form>
                          <div class="container overflow-auto" style="height:250px;">
                          <?php
                            $sql="SELECT * FROM `tb_mc_brand` ORDER BY `tb_mc_brand`.`brand` ASC";
                                                        
                            $result = mysqli_query($db,$sql);
                
                            if (mysqli_num_rows($result) > 0) 
                            {
                            foreach($result as $items)
                            {
                          ?>
                          <div class="row">
                              <div class="col text-start">
                              <svg onclick="location.href='admin_add_category.php?xbrand=<?php echo $items['brand'];?>'" class="text-danger" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-square-fill" viewBox="0 0 16 16">
                              <path d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm3.354 4.646L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 1 1 .708-.708z"></svg>
                              <?php echo $items['brand']; ?>
                              </div>
                          </div>
                          <?php
                            } 
                          } 
                          ?>
                          </div>
                      </div>
                      <div class="col-4">
                      <form method="post" action="" enctype="multipart/form-data">
                          <div class="input-group input-group-sm mb-3">
                            <select name="mcbrand" class="form-select" id="inputGroupSelect04" aria-label="Example select with button addon">
                                <?php while($row3 = mysqli_fetch_array($result3)):;?> 
                                <option class="dropdown-item" href="admin_inventory_manage.php?pcategory=<?php echo $row3['brand'];?>" value="<?php echo $row3['brand'];?>">
                                <?php echo $row3['brand'];?></option>
                                <?php endwhile; ?>
                            </select>
                            <input type="text" name="mcmodel" class="form-control" placeholder="New Motorcycle Model" aria-label="Recipient's username" aria-describedby="button-addon2">
                            <button class="btn btn-outline-secondary" type="submit" id="button-addon2">Add</button>
                          </div>
                        </form>
                          <div class="container overflow-auto" style="height:250px;">
                          <?php
                            $sql="SELECT
                            tb_mc_model.brand,
                            tb_mc_model.model
                            FROM `tb_mc_model`  
                            ORDER BY `tb_mc_model`.`brand` ASC";
                                                                
                            $result = mysqli_query($db,$sql);
                
                            if (mysqli_num_rows($result) > 0) 
                            {
                            foreach($result as $items)
                            {
                          ?>
                          <div class="row">
                              <div class="col text-start">
                              <svg onclick="location.href='admin_add_category.php?xmcbrand=<?php echo $items['brand'];?>&xmcmodel=<?php echo $items['model'];?>'" class="text-danger" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-square-fill" viewBox="0 0 16 16">
                              <path d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm3.354 4.646L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 1 1 .708-.708z"></svg>
                              <?php echo "".$items['brand']."-".$items['model'].""; ?>
                              </div>
                          </div>
                          <?php
                            } 
                          } 
                          ?>
                          </div>
                      </div>
                  </div>
                  <div class="row align-items-start mt-3">
                     <div class="col-12">
                     <form method="post" action="" enctype="multipart/form-data">
                          <div class="input-group input-group-sm mb-3">
                            <input type="text" name="id" class="form-control" placeholder="ID" aria-label="Recipient's username" aria-describedby="button-addon2">
                            <input onkeyup="this.value = this.value.toUpperCase();" type="text" name="name" class="form-control" placeholder="Company/Name" aria-label="Recipient's username" aria-describedby="button-addon2">
                            <input onkeyup="this.value = this.value.toUpperCase();" type="text" name="address" class="form-control" placeholder="Location" aria-label="Recipient's username" aria-describedby="button-addon2">
                            <input type="text" name="number" class="form-control" placeholder="Mobile" aria-label="Recipient's username" aria-describedby="button-addon2">
                          </div>
                          <div class="input-group input-group-sm mb-3">
                            <input onkeyup="this.value = this.value.toUpperCase();" type="text" name="notes" class="form-control" placeholder="Notes" aria-label="Recipient's username" aria-describedby="button-addon2">
                            <button class="btn btn-outline-secondary" type="submit" id="button-addon2">Add</button>
                          </div>
                    </form>
                          <div class="container overflow-auto" style="height:250px;">
                          <?php
                            $sql="SELECT * FROM `tb_supplier`";
                                                                
                            $result = mysqli_query($db,$sql);
                
                            if (mysqli_num_rows($result) > 0) 
                            {
                            foreach($result as $items)
                            {
                          ?>
                          <div class="row">
                              <div class="col">
                              <svg onclick="location.href='admin_add_category.php?xid=<?php echo $items['id'];?>'" class="text-danger" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-square-fill" viewBox="0 0 16 16">
                              <path d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm3.354 4.646L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 1 1 .708-.708z"></svg>
                              <?php echo $items['id']; ?> 
                              </div>
                              <div class="col-3">
                              <?php echo $items['name']; ?> 
                              </div>
                              <div class="col">
                              <?php echo $items['address']; ?>
                              </div>
                              <div class="col-5">
                              <?php echo $items['notes']; ?>
                              </div>
                              <div class="col">
                              <?php echo $items['mobile']; ?>
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