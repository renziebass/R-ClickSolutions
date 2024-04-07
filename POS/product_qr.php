<?php
include('user_session.php');
$sql1 = "SELECT
tb_products.id,
CONCAT(tb_products.category,'-',tb_products.product_brand) AS cpb,
tb_products.price,
CONCAT(tb_products.mc_brand,' ',tb_products.mc_model) AS specification
 FROM tb_products WHERE tb_products.id='".$_GET['product_id']."'";
$result1=mysqli_query($db,$sql1);
$row1 = mysqli_fetch_assoc($result1);

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
  
  if(!empty($_POST["product_id"]))
  {
    try {
      $sql2check = "SELECT * FROM tb_products
      WHERE tb_products.id='$product_id'";
      $CategoryCheck2 = mysqli_query($db, $sql2check);
      $result2 = mysqli_fetch_assoc($CategoryCheck2);
      if(empty($result2)) {
        echo '<script>alert("Product Doesnt Exist Exist!")</script>';
        header("Refresh:0");
      } else {
        header("Location: product_qr.php?product_id=$product_id&line=1");
        
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <title> <?php echo $row1['id']; ?> </title>
  </head>
  
  <body>
  <h5 class="fw-bold text-center mt-3  text-danger">PRODUCT QR CODE GENERATE</h5>
  <div class="container mb-3">
  <div class="mx-auto" style="width: 400px;">
  <form method="post" action="" enctype="multipart/form-data">
        <div class="input-group">
        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Quantity</button>
              <div class="dropdown-menu">
                  <a class="dropdown-item" href="admin_productqr.php?product_id=<?php echo $row1['id'];?>&line=1" value="">3</a>
                  <a class="dropdown-item" href="admin_productqr.php?product_id=<?php echo $row1['id'];?>&line=2" value="">6</a>
                  <a class="dropdown-item" href="admin_productqr.php?product_id=<?php echo $row1['id'];?>&line=3" value="">9</a>
                  <a class="dropdown-item" href="admin_productqr.php?product_id=<?php echo $row1['id'];?>&line=4" value="">12</a>
                  <a class="dropdown-item" href="admin_productqr.php?product_id=<?php echo $row1['id'];?>&line=5" value="">15</a>
                  <a class="dropdown-item" href="admin_productqr.php?product_id=<?php echo $row1['id'];?>&line=6" value="">18</a>
                  <a class="dropdown-item" href="admin_productqr.php?product_id=<?php echo $row1['id'];?>&line=7" value="">21</a>
                  <a class="dropdown-item" href="admin_productqr.php?product_id=<?php echo $row1['id'];?>&line=8" value="">24</a>
                  <a class="dropdown-item" href="admin_productqr.php?product_id=<?php echo $row1['id'];?>&line=9" value="">27</a>
                  <a class="dropdown-item" href="admin_productqr.php?product_id=<?php echo $row1['id'];?>&line=10" value="">30</a>
                  <a class="dropdown-item" href="admin_productqr.php?product_id=<?php echo $row1['id'];?>&line=11" value="">33</a>
                  <a class="dropdown-item" href="admin_productqr.php?product_id=<?php echo $row1['id'];?>&line=12" value="">36</a>
              </div>
          
          <input type="text" name="product_id" class="form-control" placeholder="<?php echo $_GET['product_id']; ?>">
          <button class="btn btn-outline-secondary" type="submit"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-qr-code-scan" viewBox="0 0 16 16">
                        <path d="M0 .5A.5.5 0 0 1 .5 0h3a.5.5 0 0 1 0 1H1v2.5a.5.5 0 0 1-1 0v-3Zm12 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0V1h-2.5a.5.5 0 0 1-.5-.5ZM.5 12a.5.5 0 0 1 .5.5V15h2.5a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 1 .5-.5Zm15 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1 0-1H15v-2.5a.5.5 0 0 1 .5-.5ZM4 4h1v1H4V4Z"/>
                        <path d="M7 2H2v5h5V2ZM3 3h3v3H3V3Zm2 8H4v1h1v-1Z"/>
                        <path d="M7 9H2v5h5V9Zm-4 1h3v3H3v-3Zm8-6h1v1h-1V4Z"/>
                        <path d="M9 2h5v5H9V2Zm1 1v3h3V3h-3ZM8 8v2h1v1H8v1h2v-2h1v2h1v-1h2v-1h-3V8H8Zm2 2H9V9h1v1Zm4 2h-1v1h-2v1h3v-2Zm-4 2v-1H8v1h2Z"/>
                        <path d="M12 9h2V8h-2v1Z"/>
                      </svg> QR</button>
          
          <button onclick="printDiv()" class="btn btn-outline-secondary" type="button"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer-fill" viewBox="0 0 16 16">
                <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2H5zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1z"/>
                <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2V7zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
          </svg> Print</button>
        </div>
        </form>
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
</div>
<main>

  <div class="container text-start" id="page">
    <?php
    for ($i = 0; $i < $_GET['line']; $i++) {
      include('line.php');
    }
    
  ?>                
  </div>

</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
  </body>
</html>