<?php
include('user_session.php');
$sql1="SELECT
tb_products.id,
tb_products.product_brand,
tb_products.category,
CONCAT(tb_products.mc_brand,'-',tb_products.mc_model) AS specification,
tb_products.price,
tb_products.available,
tb_products.stocks
FROM tb_products
WHERE tb_products.id='" .$_GET['id']. "'";
$result1=mysqli_query($db,$sql1);
$row1 = mysqli_fetch_assoc($result1);

function validateInput($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  
  if (empty($_POST["qty"])) {
    $qty_err = "* ";
  } else {
    $qty = validateInput($_POST["qty"]);
  }
  
  
  
  
  if(!empty($_POST["qty"]) && !empty($_GET["id"]))
  {
    try {
      
        $sql2 = "UPDATE tb_products
        SET tb_products.stocks=tb_products.stocks+'$qty',tb_products.available=tb_products.available+'$qty'
        WHERE tb_products.id='".$_GET["id"]."'";
        $RestockUpdate = mysqli_query($db, $sql2);

        $sql3 = "INSERT INTO `tb_restock_history` (`product_id`, `date`, `qty`)
        VALUES ('".$_GET["id"]."', '".date("Y-m-d H:i:s")."', '$qty')";
        $Restock = mysqli_query($db, $sql3);
        
        header("Location: admin_product_manage.php?id=".$_GET["id"]."");
      

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
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

    <title>PRODUCT: <?php echo $_GET['id']?></title>

    
  </head>
  <body style="background-color: #c7c7c7;" onload = "navbar();">
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
      
      function hide_unhide() {
        var x = document.getElementById("restockdiv");
        if (x.style.display === "none") {
          x.style.display = "block";
        } else {
          x.style.display = "none";
        }

        var x = document.getElementById("restockhistorydiv");
        if (x.style.display === "none") {
          x.style.display = "block";
        } else {
          x.style.display = "none";
        }

        var y = document.getElementById("saleshistorydiv");
        if (y.style.display === "none") {
          y.style.display = "";
        } else {
          y.style.display = "none";
        }
        
        var z = document.getElementById("text1");
        if (z.style.display === "none") {
          z.style.display = "";
        } else {
          z.style.display = "none";
        }
      }
    </script>
    <div style="background-color: #ffc067;" id="navbar"></div><!--navbar -->

    <div class="mx-5">
      <div class="m-3 pb-3 bg-light rounded">
      
      <div class="row pt-3">
        <div class="col">
        <div class="col-md-11 input-group mx-auto d-flex flex-row-reverse mb-5">
            <div class="input-group-prepend"> 
                <div class="input-group-append" id="button-addon4">
                  <button class="btn btn-outline-danger" type="button" onclick="hide_unhide()"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-plus" viewBox="0 0 16 16">
  <path d="M8 6.5a.5.5 0 0 1 .5.5v1.5H10a.5.5 0 0 1 0 1H8.5V11a.5.5 0 0 1-1 0V9.5H6a.5.5 0 0 1 0-1h1.5V7a.5.5 0 0 1 .5-.5z"/>
  <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5h-2z"/>
</svg> Re-Stock</button>
                  <button onclick="printDiv()" class="ml-2 btn btn-outline-secondary" type="button"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer-fill" viewBox="0 0 16 16">
                <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2H5zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1z"/>
                <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2V7zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
              </svg>  Print</button>
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
          </div>
        </div>
      </div>
      <div class="row" id="restockdiv" style="display:none;">
        <div class="col">
          <div class="col-md-2 mb-3 mx-auto">
          <form method="post" action="" enctype="multipart/form-data">
            
            <div class=" input-group mb-3 ">
              
                  <input name="qty" type="number" class="form-control" aria-label="Text input with dropdown button" placeholder="QTY">
                  
                  <div class="input-group-append">
                    <button class="btn btn-outline-danger" type="submit" id="button-addon2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-database-add" viewBox="0 0 16 16">
                              <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 0 1-1 0v-1h-1a.5.5 0 0 1 0-1h1v-1a.5.5 0 0 1 1 0Z"/>
                              <path d="M12.096 6.223A4.92 4.92 0 0 0 13 5.698V7c0 .289-.213.654-.753 1.007a4.493 4.493 0 0 1 1.753.25V4c0-1.007-.875-1.755-1.904-2.223C11.022 1.289 9.573 1 8 1s-3.022.289-4.096.777C2.875 2.245 2 2.993 2 4v9c0 1.007.875 1.755 1.904 2.223C4.978 15.71 6.427 16 8 16c.536 0 1.058-.034 1.555-.097a4.525 4.525 0 0 1-.813-.927C8.5 14.992 8.252 15 8 15c-1.464 0-2.766-.27-3.682-.687C3.356 13.875 3 13.373 3 13v-1.302c.271.202.58.378.904.525C4.978 12.71 6.427 13 8 13h.027a4.552 4.552 0 0 1 0-1H8c-1.464 0-2.766-.27-3.682-.687C3.356 10.875 3 10.373 3 10V8.698c.271.202.58.378.904.525C4.978 9.71 6.427 10 8 10c.262 0 .52-.008.774-.024a4.525 4.525 0 0 1 1.102-1.132C9.298 8.944 8.666 9 8 9c-1.464 0-2.766-.27-3.682-.687C3.356 7.875 3 7.373 3 7V5.698c.271.202.58.378.904.525C4.978 6.711 6.427 7 8 7s3.022-.289 4.096-.777ZM3 4c0-.374.356-.875 1.318-1.313C5.234 2.271 6.536 2 8 2s2.766.27 3.682.687C12.644 3.125 13 3.627 13 4c0 .374-.356.875-1.318 1.313C10.766 5.729 9.464 6 8 6s-2.766-.27-3.682-.687C3.356 4.875 3 4.373 3 4Z"/>
                            </svg> Add</button>
                  </div>
              </div>
          
        </form>
          </div>
        </div>
      </div>
      
      <div id="page">
      <table class="m-3 col-md-11 mx-auto table table-hover" id="saleshistorydiv">
                
                <thead >
                <h5 class="text-center text-danger"><?php echo $_GET['id']?></h5>
                <div class="col-md-11 container mx-auto">
                    <div class="row">
                      <div class="col-sm text-center mb-0 font-weight-bold">
                      <?php echo $row1['product_brand']; ?>
                      </div>
                      <div class="col-sm text-center mb-0 font-weight-bold">
                      <?php echo $row1['category']; ?>
                      </div>
                      <div class="col-sm text-center mb-0 font-weight-bold">
                      <?php echo $row1['specification']; ?>
                      </div>
                      <div class="col-sm text-center mb-0 font-weight-bold">
                      <?php echo $row1['stocks']; ?>
                      </div>
                      <div class="col-sm text-center mb-0 font-weight-bold">
                      <?php echo $row1['available']; ?>
                      </div>
                      <div class="col-sm text-center mb-0 font-weight-bold">
                      <?php echo $row1['price']; ?>
                      </div>
                    </div>
                </div>
                <div class="col-md-11 container mx-auto mb-3">
                    <div class="row">
                      <div class="col-sm text-center mt-0 text-muted">
                        Product Brand
                      </div>
                      <div class="col-sm text-center text-muted">
                        Category
                      </div>
                      <div class="col-sm text-center text-muted">
                        Specification
                      </div>
                      <div class="col-sm text-center text-muted">
                        Stocks
                      </div>
                      <div class="col-sm text-center text-muted">
                        Available
                      </div>
                      <div class="col-sm text-center text-muted">
                        Price
                      </div>
                    </div>
                </div>
                <h5 class="text-center" id="text1">SALES HISTORY</h5>
                
                  <tr class="text-muted">
                    <th scope="col">Transaction ID</th>
                    <th scope="col">Date</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">Total</th>
                  </tr>
                </thead>
                <tbody>
                  
                <?php
                  $sql="SELECT
                  tb_transactions.id,
                  tb_transactions.date,
                  COUNT(tb_cart.product_id) AS quantity,
                  SUM(tb_products.price) AS total
                  FROM tb_products 
                  LEFT JOIN tb_cart ON tb_products.id=tb_cart.product_id
                  RIGHT JOIN tb_transactions ON tb_cart.transaction_id=tb_transactions.id
                  WHERE tb_products.id='" .$_GET['id']. "'
                  GROUP BY tb_transactions.id";
                                                      
                  $result = mysqli_query($db,$sql);
      
                  if (mysqli_num_rows($result) > 0) 
                  {
                  foreach($result as $items)
                  {
                ?>
                      <tr>  
                      <td><a class="" onclick="location.href='admin_transaction.php?id=<?php echo $items['id'];?>'"><?php echo $items['id'];?></a></td>
                      <td><?php echo $items['date']; ?></td>
                      <td><?php echo $items['quantity']; ?></td>
                      <td><?php echo $items['total']; ?></td>
                      </tr>
                <?php
                  } 
                } 
                  ?>
                </tbody>
      </table>
      <table class="m-3 col-md-11 mx-auto table table-hover" id="restockhistorydiv" style="display:none;">
                
                <thead >
                <h5 class="text-center text-danger">RE-STOCK HISTORY <?php echo $_GET['id']?></h5>

                <tr class="text-muted">
                    <th scope="col">Date & Time</th>
                    <th scope="col">QTY</th>
                    
                  </tr>
                </thead>
                <tbody>
                  
                <?php
                  $sql="SELECT tb_restock_history.product_id,
                  CONCAT(tb_products.category,'-',tb_products.mc_brand,' ',tb_products.mc_model) AS specification,
                  tb_products.product_brand,
                  tb_restock_history.date,
                  tb_restock_history.qty
                  FROM tb_restock_history
                  JOIN tb_products ON tb_restock_history.product_id=tb_products.id
                  WHERE tb_restock_history.product_id='" .$_GET['id']. "'
                  ORDER BY tb_restock_history.date DESC";
                                                      
                  $result = mysqli_query($db,$sql);
      
                  if (mysqli_num_rows($result) > 0) 
                  {
                  foreach($result as $items)
                  {
                ?>
                      <tr>
                      <td><?php echo $items['date']; ?></td>
                      <td><?php echo $items['qty']; ?></td>
                      </tr>
                <?php
                  } 
                } 
                  ?>
                </tbody>
      </table> 
      </div>

      

      </div><!-- last div-->
      <footer class="m-3 mt-4 text-muted border-top">
      <p>&copy; R-Click Solutions 2023</p>
      </footer>
    </div>
    

    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: jQuery and Bootstrap Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>
    -->
  </body>
</html>