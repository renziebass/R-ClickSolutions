<?php
include('user_session.php');

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
      COUNT(tb_cart.product_id) as quantity,
      SUM(tb_cart.price) AS total
      FROM tb_cart
      LEFT JOIN tb_transactions ON tb_cart.transaction_id=tb_transactions.id
      WHERE tb_transactions.id='" .$_GET['id']. "' AND tb_transactions.status='unpaid'
      GROUP BY tb_cart.product_id) AS B
      ON A.id=B.product_id
      GROUP BY B.product_id";
                                                      
$result = mysqli_query($db,$sql);

if (mysqli_num_rows($result) > 0) {
  $voidButton = "disabled";
} else {
  $voidButton = null;
}

$sql1="SELECT   
tb_transactions.name,
(SELECT COUNT(tb_cart.transaction_id)
FROM tb_cart
WHERE tb_cart.transaction_id='" .$_GET['id']. "') AS items,
SUM(tb_cart.price) AS total,
CONCAT(DATE_FORMAT(tb_transactions.date,'%M %d,%Y'),'  ',tb_transactions.time) AS date_time
FROM tb_cart LEFT JOIN tb_transactions ON tb_cart.transaction_id=tb_transactions.id
WHERE tb_cart.transaction_id='" .$_GET['id']. "'
GROUP BY tb_cart.transaction_id";
$result1=mysqli_query($db,$sql1);
$row1 = mysqli_fetch_assoc($result1);

if((empty($_GET['id']))) {
  header("Location: admin_unpaid.php?date=".date("Y-m-d")."");

} 

if((empty($_GET['DeleteProduct'])) && (empty($_GET['QTY']))) {

} else {
  $sql2="DELETE FROM tb_cart WHERE tb_cart.product_id='" .$_GET['DeleteProduct']. "' AND tb_cart.transaction_id='" .$_GET['id']. "'";
  $sql3="UPDATE tb_products
  SET tb_products.available=tb_products.available+'" .$_GET['QTY']. "'
  WHERE tb_products.id='" .$_GET['DeleteProduct']. "'";

  if (($db->query($sql2)) && ($db->query($sql3)) === TRUE) {
    echo "Record updated successfully";
  } else {
    echo "Error updating record: " . $db->error;
  }
}

if((empty($_GET['VoidID']))) {

} else {
  $sql4="DELETE FROM tb_transactions WHERE tb_transactions.id='" .$_GET['VoidID']. "'";
  

if ($db->query($sql4) === TRUE) {
    echo "Transaction Void Successfully";

  } else {
    echo "Error Voiding Transaction: " . $db->error;
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

    <title><?php echo "UNPAID: ".$row1['name'].""?></title>

    
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
    </script>
    <div style="background-color: #ffc067;" id="navbar"></div><!--navbar -->

    <div class="mx-5">
      <div class="m-3 pb-3 bg-light rounded" style="min-height: 600px;">
      
      <div class="row pt-3">
        <div class="col">
        <div class="col-md-11 input-group mb-5 mx-auto d-flex flex-row-reverse">
            <div class="input-group-prepend"> 
                <div class="input-group-append" id="button-addon4">
                  <button class="btn btn-outline-danger" type="button" onclick="location.href='admin_unpaid_transaction.php?VoidID=<?php echo $_GET['id']?>'" <?php echo $voidButton; ?>><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16">
                    <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5ZM11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H2.506a.58.58 0 0 0-.01 0H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1h-.995a.59.59 0 0 0-.01 0H11Zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5h9.916Zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47ZM8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5Z"/>
                  </svg> Void</button>
                  <button onclick="printDiv()" class="ml-2 btn btn-outline-secondary" type="button"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer-fill" viewBox="0 0 16 16">
                    <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2H5zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1z"/>
                    <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2V7zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                  </svg> Print</button>
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
                  <button class="ml-2 btn btn-outline-secondary" type="button"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bag-check" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M10.854 8.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708 0z"/>
  <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1zm3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4h-3.5zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V5z"/>
</svg> Pay</button>
                </div>
            </div>
          </div>
        </div>
      </div>

      <div id="page">
      <table class="m-3 col-md-11 mx-auto table table-hover">
                
                <thead >
                <h5 class="text-center text-danger">
                <?php
                if(empty($row1['name']) && ($_GET['id'])) {
                  echo "No Products, Please VOID this TRANSACTION";
                } else {
                  echo "UNPAID ACCOUNT OF ".$row1['name']." ".$_GET['id']."";
                }
              
                ?>
                </h5>
                <div class="col-md-8 container mx-auto">
                    <div class="row">
                      <div class="col-sm text-center mb-0 font-weight-bold">
                      <?php
                      if(empty($row1['date_time'])) {
                        $date_time ="N/A";
                      } else {
                        $date_time = $row1['date_time'];
                      }
                      echo $date_time;
                      ?>
                      </div>
                      <div class="col-sm text-center mb-0 font-weight-bold">
                      <?php
                      if(empty($row1['items'])) {
                        $items ="0";
                      } else {
                        $items = $row1['items'];
                      }
                      echo $items;
                      ?>
                      </div>
                      <div class="col-sm text-center mb-0 font-weight-bold">
                      <?php
                      if(empty($row1['total'])) {
                        $total ="0";
                      } else {
                        $total = $row1['total'];
                      }
                      echo $total;
                      ?>
                      </div>
                    </div>
                </div>
                <div class="col-md-8 container mx-auto">
                    <div class="row">
                      <div class="col-sm text-center mt-0 text-muted">
                        Date & Time
                      </div>
                      <div class="col-sm text-center text-muted">
                        Total Items
                      </div>
                      <div class="col-sm text-center text-muted">
                        Total Unpaid Amount
                      </div>
                    </div>
                </div>
                
                  <tr class="text-muted">
                    <th scope="col">Product ID</th>
                    <th scope="col">BRAND</th>
                    <th scope="col">Specification</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">Price</th>
                    <th scope="col">Total</th>
                    <th scope="col"></th>
                  </tr>
                </thead>
                <tbody>
                  
                <?php
                  
      
                  if (mysqli_num_rows($result) > 0) 
                  {
                  foreach($result as $items)
                  {
                ?>
                      <tr>  
                      <td><?php echo $items['product_id']; ?></td>
                      <td><?php echo $items['product_brand']; ?></td>
                      <td><?php echo $items['specification']; ?></td>
                      <td><?php echo $items['quantity']; ?></td>
                      <td><?php echo $items['price']; ?></td>
                      <td><?php echo $items['total']; ?></td>
                      <td><svg onclick="location.href='admin_unpaid_transaction.php?id=<?php echo $_GET['id']?>&DeleteProduct=<?php echo $items['id'];?>&QTY=<?php echo $items['quantity'];?>'" class="text-danger" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-square-fill" viewBox="0 0 16 16">
                        <path d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm3.354 4.646L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 1 1 .708-.708z"/>
                      </svg></td>
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