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
  header("Location: admin_transactions.php?date=".date("Y-m-d")."");

} 

if((empty($_GET['DeleteProduct'])) && (empty($_GET['QTY']))) {

} else {
  $sql2="DELETE FROM tb_cart WHERE tb_cart.product_id='" .$_GET['DeleteProduct']. "' AND tb_cart.transaction_id='" .$_GET['id']. "'";
  $sql3="UPDATE tb_products
  SET tb_products.available=tb_products.available+'" .$_GET['QTY']. "'
  WHERE tb_products.id='" .$_GET['DeleteProduct']. "'";

  if (($db->query($sql2)) && ($db->query($sql3)) === TRUE) {
    echo "Record updated successfully";
    header("Location: admin_unpaid_transaction.php?id=".$_GET['id']."");
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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UNPAID ID : <?php echo $_GET['id']; ?></title>
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
                    <div class="col-6 d-flex flex-row">
                        <div class="btn-group">
                        <button type="button" onclick="location.href='admin_transaction.php?VoidID=<?php echo $_GET['id']?>'" class="btn btn-danger btn-sm" <?php echo $voidButton; ?>>Void</button>
                        </div>
                    </div>
                    <div class="col-6 d-flex flex-row-reverse">
                    <button type="button" onclick="printDiv();" class="btn btn-secondary btn-sm">Print</button>
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
                <div class="row align-items-start overflow-auto mt-3" id="page" style="height: 620px;">
                    <div class="col-12" id="">
                        <div class="col text-danger">
                        <?php
                        if(empty($row1['name']) && ($_GET['id'])) {
                          echo "No Products, Please VOID this TRANSACTION";
                        } else {
                          echo "UNPAID ACCOUNT OF ".$row1['name']." ".$_GET['id']."";
                        }
                        ?>
                        </div>
                        <div class="col text-muted">
                           Date & Time : <?php
                          if(empty($row1['date_time'])) {
                            $date_time ="N/A";
                          } else {
                            $date_time = $row1['date_time'];
                          }
                          echo $date_time;
                          ?>, Total Items : <?php
                          if(empty($row1['items'])) {
                            $items ="0";
                          } else {
                            $items = $row1['items'];
                          }
                          echo $items;
                          ?>, Total Unpaid Amount <?php
                          if(empty($row1['total'])) {
                            $total ="0";
                          } else {
                            $total = $row1['total'];
                          }
                          echo $total;
                      ?>
                        </div>
                        <div class="row">
                            <div class="col-12 mt-3">
                                <div class="row">
                                    <div class="col">
                                    </div>
                                    <div class="col text-muted">
                                        PRODUCT ID
                                    </div>
                                    <div class="col text-muted">
                                        BRAND
                                    </div>
                                    <div class="col text-muted">
                                        SPECIFICATION
                                    </div>
                                    <div class="col text-muted">
                                        QTY
                                    </div>
                                    <div class="col text-muted">
                                        PRICE
                                    </div>
                                    <div class="col text-muted">
                                        TOTAL
                                    </div>
                                </div>
                                <?php
                                  if (mysqli_num_rows($result) > 0) 
                                  {
                                  foreach($result as $items)
                                  {
                                ?>
                                <div class="row">
                                    <div class="col">
                                    <svg onclick="location.href='admin_transaction.php?id=<?php echo $_GET['id']?>&DeleteProduct=<?php echo $items['id'];?>&QTY=<?php echo $items['quantity'];?>'" class="text-danger" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-square-fill" viewBox="0 0 16 16">
                                        <path d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm3.354 4.646L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 1 1 .708-.708z"/>
                                    </svg>
                                    </div>
                                    <div class="col">
                                    <a class="" onclick="location.href='admin_product.php?id=<?php echo $items['product_id'];?>'"><?php echo $items['product_id'];?></a>
                                    </div>
                                    <div class="col">
                                    <?php echo $items['product_brand']; ?>
                                    </div>
                                    <div class="col">
                                    <?php echo $items['specification']; ?>
                                    </div>
                                    <div class="col">
                                    <?php echo $items['quantity']; ?>
                                    </div>
                                    <div class="col">
                                    <?php echo $items['price']; ?>
                                    </div>
                                    <div class="col">
                                    <?php echo $items['total']; ?>
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