<?php
include('user_session.php');
$sql1 = "SELECT 
tb_products.id,
tb_products.price,
CONCAT(tb_products.category,' ',tb_products.mc_brand,' ',tb_products.mc_model) AS specification
 FROM tb_products WHERE tb_products.id='".$_GET['product_id']."'";
$result1=mysqli_query($db,$sql1);
$row1 = mysqli_fetch_assoc($result1);


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
  <h3 class="fw-bold text-center mt-3  text-danger">PRODUCT QR CODE GENERATE</h3>
  <div class="container mb-3">
  <div class="mx-auto" style="width: 200px;">
        <div class="input-group">
              <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Quantity</button>
              <div class="dropdown-menu">
                  <a class="dropdown-item" href="product_qr.php?product_id=<?php echo $row1['id'];?>&line=1" value="">3</a>
                  <a class="dropdown-item" href="product_qr.php?product_id=<?php echo $row1['id'];?>&line=2" value="">6</a>
                  <a class="dropdown-item" href="product_qr.php?product_id=<?php echo $row1['id'];?>&line=3" value="">9</a>
                  <a class="dropdown-item" href="product_qr.php?product_id=<?php echo $row1['id'];?>&line=4" value="">12</a>
                  <a class="dropdown-item" href="product_qr.php?product_id=<?php echo $row1['id'];?>&line=5" value="">15</a>
                  <a class="dropdown-item" href="product_qr.php?product_id=<?php echo $row1['id'];?>&line=6" value="">18</a>
                  <a class="dropdown-item" href="product_qr.php?product_id=<?php echo $row1['id'];?>&line=7" value="">21</a>
                  <a class="dropdown-item" href="product_qr.php?product_id=<?php echo $row1['id'];?>&line=8" value="">24</a>
                  <a class="dropdown-item" href="product_qr.php?product_id=<?php echo $row1['id'];?>&line=9" value="">27</a>
                  <a class="dropdown-item" href="product_qr.php?product_id=<?php echo $row1['id'];?>&line=10" value="">30</a>
                  <a class="dropdown-item" href="product_qr.php?product_id=<?php echo $row1['id'];?>&line=11" value="">33</a>
                  <a class="dropdown-item" href="product_qr.php?product_id=<?php echo $row1['id'];?>&line=12" value="">36</a>
              </div>
              <button onclick="printDiv()" class="btn btn-outline-secondary" type="submit">Print</button>
        </div>
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