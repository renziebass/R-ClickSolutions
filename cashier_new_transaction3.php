<?php
include('user_session.php');
$dateS=date_create(date("Y-m-d"));
$TR = $_SESSION['id']."-".date_format($dateS,"Ymd")."-".date("His");

$btn_disc = "hidden";
$btn_save = "hidden";
$btn_cash = "hidden";
$btn_gcash = "hidden";
$disc_msg = null;
$btn_void = null;

$sql1 = "SELECT
CONCAT(tb_products.category,' ',tb_products.product_brand,' ',tb_products.mc_brand,' ',tb_products.mc_model)
AS specification,
tb_products.product_brand
FROM tb_products";
$result1=mysqli_query($db,$sql1);

$sql3 = "SELECT * FROM `tb_product_category`";
$result3=mysqli_query($db,$sql3);

$sql4 = "SELECT * FROM `tb_products`";
$result4=mysqli_query($db,$sql4);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (!empty($_POST["search"])) {
    $search = $_POST["search"];
    header("Cache-Control: no cache");
  } else {
    $search = "renzie";
  }
} else {
  $search = "renzie";
}

if(!empty($_GET['id'])) {

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

  if(empty($row2['total2'])) {
    $DeleteTR = "DELETE FROM tb_transactions WHERE tb_transactions.id='" .$_GET['id']. "'";
    $X = mysqli_query($db, $DeleteTR);
  }

  $cart_button = "btn_qty"; 
} else {
  $cart_button = "btn_tr";
}

if(empty($row2['total2'])) {
  $total ="0";
} else {
  
  $btn_save = null;
  $btn_cash = null;
  $btn_gcash = null;
  $btn_disc = null;


    if(!empty($_GET['disc'])){
      $cart_button = null;
      $btn_save = "hidden";
      $btn_void = "hidden";
      $btn_disc = "hidden";
    
      $sql2d="SELECT * FROM tb_discount WHERE tb_discount.id <= '".$_GET['disc']."'";
      $result2d=mysqli_query($db,$sql2d);
      $row2d = mysqli_fetch_assoc($result2d);
      
      
      if ($row2d['cap'] <= $row2['total2'] * $row2d['percent']) {
        $less = $row2d['cap'];
      } else {
        $less = $row2['total2'] * $row2d['percent'];
      }
    
      $disc_msg = "Discount ".$less;
      
      $total = $row2['total2'] - $less;
    } else {
      $total = $row2['total2'];
    }
}
$sql2b="SELECT * FROM tb_discount WHERE tb_discount.min <= '".$total."'";
$result2b=mysqli_query($db,$sql2b);

$sql2c="SELECT * FROM tb_payment_methods";
$result2c=mysqli_query($db,$sql2c);

  


if ($_SERVER["REQUEST_METHOD"] == "GET") {
  if(!empty($_GET["product_id2"]) && !empty($_GET["quantity2"])) {

      $sql6a = "DELETE FROM `tb_cart`
      WHERE tb_cart.transaction_id='" .$_GET['id']. "'
      AND tb_cart.product_id='" .$_GET['product_id2']. "'";

      $sql6b = "UPDATE tb_products
      SET tb_products.available=tb_products.available+'" .$_GET['quantity2']. "'
      WHERE tb_products.id='" .$_GET['product_id2']. "'";

      $DeleteProduct = mysqli_query($db, $sql6a);
      $ReturnProduct = mysqli_query($db, $sql6b);
     
      header("refresh:0.1;url=cashier_new_transaction3.php?id=".$_GET['id']."&date=".$_GET['date']."");
  
}
if(!empty($_GET["product_id"]) && !empty($_GET["price"] && !empty($_GET["quantity"]))) {

        $sql5b="SELECT tb_products.disc
        FROM tb_products
        WHERE tb_products.id='".$_GET["product_id"]."'";
        $checkdisc=mysqli_query($db,$sql5b);
        $row5 = mysqli_fetch_assoc($checkdisc);

        $sql5ccheck = "SELECT *
        FROM tb_transactions
        WHERE tb_transactions.id='" .$_GET['id']. "'";
        $checktr=mysqli_query($db,$sql5ccheck);
        $resulttr = mysqli_fetch_assoc($checktr);

        $price2 = $_GET["price"]-$row5['disc'];
        $total = $_GET["price"] * $_GET["quantity"];

        $sql4 = "INSERT INTO `tb_cart` (`transaction_id`, `date`, `product_id`, `quantity`, `price`, `total`)
        VALUES ('" .$_GET['id']. "', '".$_GET['date']."', '".$_GET["product_id"]."', '".$_GET["quantity"]."', '$price2', '$total')";

        $sql4b = "UPDATE tb_products
        SET tb_products.available=tb_products.available-'".$_GET["quantity"]."'
        WHERE tb_products.id='".$_GET["product_id"]."'";

        function function_sound() {
        echo "<script>
              var audio = new Audio('https://rclickpos.com/beep.wav');
              audio.play();
              </script>";
        header("refresh:0.1;url=cashier_new_transaction3.php?id=".$_GET['id']."&date=".$_GET['date']."");
        }

        if(empty($resulttr['id'])) {
          $sql8a = "INSERT INTO tb_transactions (id, date,time, status) VALUES
          ('" .$_GET['id']. "','".$_GET['date']."','".date("H:i:s")."','unpaid')";
          $SaveTRa = mysqli_query($db, $sql8a);
        }

        $AddProduct = mysqli_query($db, $sql4);
        $UpdateProduct = mysqli_query($db, $sql4b);
        function_sound();

  }
  if(!empty($_POST["name"]))
  {
    try {
      
    }
    catch(PDOException $e)
      {
        echo $sql1 . "<br>" . $e->getMessage();
      }
    $db=null;
  }

}
if(!empty($_GET['name'])){

    $sql8 = "UPDATE tb_transactions 
    SET tb_transactions.name='" . $_GET['name'] . "'
    WHERE tb_transactions.id='" .$_GET['id']. "'";
    $SaveTR = mysqli_query($db, $sql8);

    header("refresh:0.5;url=cashier_dashboard.php");

}
if(!empty($_GET['payment'])){

      $sqlcheck2 = "SELECT * FROM tb_payments
      WHERE tb_payments.id='" .$_GET['id']. "'";
      $Paymentcheck = mysqli_query($db, $sqlcheck2);
      $resultCheck2 = mysqli_fetch_assoc($Paymentcheck);

      if(empty($resultCheck2))  {

        $sql9="INSERT INTO tb_payments (id, date,time, total, payment, payment1, change1)
        VALUES ('" .$_GET['id']. "','".$_GET['date']."','".date("H:i:s")."','" . $total . "','" . $_GET["payment"] . "','".$_GET['payment1']."','" . $_GET["payment"]-$total . "')";

        $sql10 = "UPDATE tb_transactions 
        SET tb_transactions.status='paid'
        WHERE tb_transactions.id='".$_GET['id']."'";

        $SaveTR2a = mysqli_query($db, $sql9);
        $SaveTR2b = mysqli_query($db, $sql10);

        header( "refresh:0.5;url=cashier_new_transaction3.php?date=".$_GET['date']."" );
      
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
    <title>
      <?php
      if(empty($row2['name'])) {
      $name ="NEW TRANSACTION";
      } else {
      $name = $row2['name'];
      $btn_save = "hidden";
      }
      echo $name;
      ?>
      </title>
 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <style>
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
  <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="cashier_dashboard.php">R-Click POS: Karaang Garahe</a>
  <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6 text-end">
      WHOLESALE TRANSACTION
  </a>
</header>

<main class="ms-sm-auto" >
  <div class="container-fluid">
    <div class="row">
<div class="col">
<div class="">
    <div class="row" id="click">
      <div class="col">
        <div class="card-body">
          <div class="float-end">
            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-list-check" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5M3.854 2.146a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708L2 3.293l1.146-1.147a.5.5 0 0 1 .708 0m0 4a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708L2 7.293l1.146-1.147a.5.5 0 0 1 .708 0m0 4a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0"/>
            </svg>
          </div>
              <h6 class="text-muted fw-normal" title="Number of Customers">Items</h6>
              <h3 class="">
                <?php
                if(empty($row2['items'])) {
                $items ="0";
                } else {
                $items = $row2['items'];
                }
                echo $items;
                ?>
              </h3>
                  
        </div>
      </div>
      
      <div class="col">
        
        <div class="card-body">
            <div class="float-end text-success">
              <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-currency-exchange" viewBox="0 0 16 16">
                <path d="M0 5a5 5 0 0 0 4.027 4.905 6.5 6.5 0 0 1 .544-2.073C3.695 7.536 3.132 6.864 3 5.91h-.5v-.426h.466V5.05q-.001-.07.004-.135H2.5v-.427h.511C3.236 3.24 4.213 2.5 5.681 2.5c.316 0 .59.031.819.085v.733a3.5 3.5 0 0 0-.815-.082c-.919 0-1.538.466-1.734 1.252h1.917v.427h-1.98q-.004.07-.003.147v.422h1.983v.427H3.93c.118.602.468 1.03 1.005 1.229a6.5 6.5 0 0 1 4.97-3.113A5.002 5.002 0 0 0 0 5m16 5.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0m-7.75 1.322c.069.835.746 1.485 1.964 1.562V14h.54v-.62c1.259-.086 1.996-.74 1.996-1.69 0-.865-.563-1.31-1.57-1.54l-.426-.1V8.374c.54.06.884.347.966.745h.948c-.07-.804-.779-1.433-1.914-1.502V7h-.54v.629c-1.076.103-1.808.732-1.808 1.622 0 .787.544 1.288 1.45 1.493l.358.085v1.78c-.554-.08-.92-.376-1.003-.787zm1.96-1.895c-.532-.12-.82-.364-.82-.732 0-.41.311-.719.824-.809v1.54h-.005zm.622 1.044c.645.145.943.38.943.796 0 .474-.37.8-1.02.86v-1.674z"/>
              </svg>
            </div>
                <h6 class="text-muted fw-normal " title="Number of Customers">Total</h6>
                <h3 class=" text-success">
                  <?php
                  
                 
                

                  echo $total;
                  ?>
                </h3>
                <div>
                <p class="text-danger text-end" title="Remove Discount" <?php if(empty($_GET['disc'])) { echo "hidden"; }?>>
                <?php echo $disc_msg; ?>
                  <span>
                    <button class="btn btn-sm p-0 m-0"
                    onclick="location.href='cashier_new_transaction3.php?id=<?php echo $_GET['id'];?>&date=<?php echo  $_GET['date']?>'"> 
                      <svg class="text-danger" xmlns="http://www.w3.org/2000/svg" width="27" height="30" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
                        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
                      </svg>
                    </button>
                  </span>
                </p>
                
                </div>
                
        </div>
      </div>
    </div>
   
    <table class="table table-hover table-sm table-borderless mt-2 mb-5">
          <tbody>
            <?php
            if(!empty($_GET['id'])) {
              $sql="SELECT *
              FROM (SELECT
                  tb_products.id,
                  CONCAT(tb_products.category,' ',tb_products.product_brand,' ',tb_products.mc_brand,' ',tb_products.mc_model)AS specification
                  FROM tb_cart LEFT JOIN tb_products ON tb_cart.product_id=tb_products.id) AS A
              JOIN (SELECT
                  tb_cart.product_id,
                  SUM(tb_cart.quantity) AS quantity,
                  FORMAT(SUM(tb_cart.price), 2) AS price,
                  FORMAT(SUM(tb_cart.price*tb_cart.quantity), 2) AS total
                  FROM tb_cart WHERE tb_cart.transaction_id='".$_GET['id']."'
                  GROUP BY tb_cart.product_id) AS B
              ON A.id=B.product_id
              GROUP BY B.product_id";
            } else {
              $sql="SELECT *
                FROM (SELECT
                    tb_products.id,
                    CONCAT(tb_products.category,' ',tb_products.product_brand,' ',tb_products.mc_brand,' ',tb_products.mc_model)AS specification
                    FROM tb_cart LEFT JOIN tb_products ON tb_cart.product_id=tb_products.id) AS A
                JOIN (SELECT
                    tb_cart.product_id,
                    SUM(tb_cart.quantity) AS quantity,
                    FORMAT(SUM(tb_cart.price), 2) AS price,
                    FORMAT(SUM(tb_cart.price*tb_cart.quantity), 2) AS total
                    FROM tb_cart WHERE tb_cart.transaction_id=''
                    GROUP BY tb_cart.product_id) AS B
                ON A.id=B.product_id
                GROUP BY B.product_id";
            }

                
                                                                                    
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
              <td>
              <button type="button" title="Void & Return Product" class="btn btn-sm p-0 m-0" 
                          onclick="btn_void(this.getAttribute('data-1'), this.getAttribute('data-2'), 
                          this.getAttribute('data-3'))"
                          data-1="<?php echo $items['id']; ?>"
                          data-2="<?php echo $items['specification']; ?>"
                          data-3="<?php echo $items['quantity']; ?>"
                          <?php echo $btn_void;?>>
                          <span>
                            <svg  class="text-danger" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-x-square-fill" viewBox="0 0 16 16">
                            <path d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm3.354 4.646L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 1 1 .708-.708z"/>
                            </svg>
                          </span>
                        </button>

              </td>
           
            </tr>
            <?php
            } 
            } else {
              $button="disabled";
            }
            ?>
          </tbody>
        </table>   
  </div>
    <form method="post" enctype="multipart/form-data" autocomplete=off>
      <div>
          <div class="input-group mt-3">
          <button class="btn btn-secondary" id="button-addon2"><span data-feather="camera" class="align-text-end"></button>
          
          <div class="autocomplete col">
          <input id="search" onkeyup="this.value = this.value.toUpperCase();" name="search" class="form-control" list="datalistOptions" id="exampleDataList" placeholder="ALT + X to Search Product">
          </div>
           
            <button class="btn btn-secondary" type="submit" id="button-addon2"><span data-feather="search" class="align-text-end"></button>
          </div>
      </div>
      </form>
    <table class="table table-hover table-borderless table-sm mt-3 mb-5" id="tblFocus">
          <thead>
            <tr class="text-muted">
              <th scope="col">Specification</th>
              <th scope="col">Stocks</th>
              <th scope="col">Price 1</th>
              <th scope="col">Price 2</th>
              <th scope="col">Price 3</th>
              <th scope="col">Discount</th>
            </tr>
          </thead>
          <tbody>
            <?php
                $sqlb="SELECT
                tb_products.id,
                tb_products.product_brand,
                tb_products.disc,
                CONCAT(tb_products.category,' ',tb_products.product_brand,' ',tb_products.mc_brand,' ',tb_products.mc_model) AS specification,
                CONCAT(tb_products.available,'/',tb_products.stocks) AS stocks,
                tb_products.available,
                CONCAT(tb_products.price1) AS price1,
                CONCAT(tb_products.price2) AS price2,
                CONCAT(tb_products.price3) AS price3,
                CASE WHEN tb_products.available=0
                THEN 'text-danger' ELSE null END AS textcolor
                FROM tb_products
                WHERE tb_products.category LIKE '%".$search."%' 
                OR tb_products.mc_brand LIKE '%".$search."%' 
                OR tb_products.mc_model LIKE '%".$search."%' 
                OR tb_products.product_brand LIKE '%".$search."%'
                OR tb_products.id LIKE '%".$search."%'
                OR CONCAT(tb_products.category,' ',tb_products.product_brand,' ',tb_products.mc_brand,' ',tb_products.mc_model) LIKE '%".$search."%'";
                                                                                    
                $resultb = mysqli_query($db,$sqlb);

                if (mysqli_num_rows($resultb) > 0) 
                {
                foreach($resultb as $itemsb)
                {
            ?>
            <tr class="<?php echo $itemsb['textcolor']; ?>"
            title="Click to add-to-Cart"
            onclick="<?php echo $cart_button;?>(this.getAttribute('data-1'), this.getAttribute('data-2'), 
            this.getAttribute('data-3'), this.getAttribute('data-4'), this.getAttribute('data-5'), this.getAttribute('data-6'))"
            data-1="<?php echo $itemsb['specification']; ?>" 
            data-2="<?php echo $itemsb['id']; ?>" 
            data-3="<?php echo $itemsb['price1']; ?>"
            data-4="<?php echo $itemsb['available']; ?>"
            data-5="<?php echo $itemsb['price2']; ?>"
            data-6="<?php echo $itemsb['price3']; ?>"> 

                <td><?php echo $itemsb['specification']; ?></td>
                <td><?php echo $itemsb['stocks']; ?></td>
                <td><?php echo $itemsb['price1']; ?></td>
                <td><?php echo $itemsb['price2']; ?></td>
                <td><?php echo $itemsb['price3']; ?></td>
                <td><?php echo $itemsb['disc']; ?></td>
            </tr>
            <?php
            } 
            } 
            ?>
          </tbody>
          <script>
             
          </script>
        </table>
      
    </div> 
    </div>
  </div>
   
  
</main>


<div class="position-fixed bottom-0 end-0 translate-bottom p-3 bg-white">
<form>
<button type="button" class="btn btn-success btn-sm" accesskey="c" onclick="window.open('cashier_new_transaction3.php?id=<?php echo $TR;?>&date=<?php echo date('Y-m-d')?>')" <?php echo $button;?>>NEW TR</button>
    <button type="button" class="btn btn-danger btn-sm" accesskey="m" onclick="btn_save()" <?php echo $btn_save;?>>SAVE</button>
    <button type="button" class="btn btn-warning btn-sm" accesskey="," onclick="btn_disc()" <?php echo $btn_disc;?>>DISCOUNT</button>
    <button type="button" class="btn btn-success btn-sm" accesskey="." onclick="btn_cash()" <?php echo $btn_cash;?>>CASH</button>
    <button type="button" class="btn btn-primary btn-sm" accesskey="/" onclick="btn_other()" <?php echo $btn_gcash;?>>OTHER</button>
</form>
</div>

  <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@4/dark.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
  <script>
    var input = document.getElementById('search');
    var input2 = document.getElementById('click');
    var message = document.getElementsByClassName('table')[0];
    input.addEventListener('focus', function() {
        message.style.display = 'none';
    });
    input.addEventListener('focusout', function() {
        message.style.display = 'none';
    });
    input2.addEventListener('click', function() {
        message.style.display = '';
    });


    window.onload = init;
    function init(){
    document.getElementById("search").focus();
    }
    
    function btn_save() {
      const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
          confirmButton: "btn btn-success",
          cancelButton: "btn btn-secondary me-1"
        },
        buttonsStyling: false
      });
      swalWithBootstrapButtons.fire({
        title: "Enter Customer Name",
        input: "text",
        showCancelButton: true,
        confirmButtonText: "Save",
        cancelButtonText: "Cancel",
        reverseButtons: true,
        inputValidator: (result) => {
          if (!result) {
            return "Enter name!";
          } else {
          }
        },
        inputAttributes: {
        maxlength: "30"
      }
      }).then((result) => {
        if (result.isConfirmed) {
          swalWithBootstrapButtons.fire({
            title: "Saved "+result.value.toUpperCase(),
            icon: "success",
            showConfirmButton: false,
            timer: 1500
          }).then((result2) => {
            if (result2.dismiss === swalWithBootstrapButtons.DismissReason.timer) {
              window.location.href = window.location.href+'&name='+result.value.toUpperCase();
            }
           });
        } else {
          result.dismiss === Swal.DismissReason.cancel
        }
      });
    }
    function btn_void(data_1,data_2,data_3) {
      const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
          confirmButton: "btn btn-danger",
          cancelButton: "btn btn-secondary me-1"
        },
        buttonsStyling: false
      });
      swalWithBootstrapButtons.fire({
        title: "VOID "+data_2+" ?",
        showCancelButton: true,
        confirmButtonText: "Void",
        cancelButtonText: "Cancel",
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = window.location.href+'&product_id2='+data_1+'&quantity2='+data_3;
        } else {
          result.dismiss === Swal.DismissReason.cancel
        }
      });
    }
    function btn_qty(data_1,data_2,data_3,data_4,data_5,data_6) {
      const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
          confirmButton: "btn btn-success",
          cancelButton: "btn btn-secondary me-1"
        },
        buttonsStyling: false
      });
      swalWithBootstrapButtons.fire({
        title: data_1,
        input: "text",
        footer: '<h6 class="text-white text-center">Available stocks: '+data_4+'</h6>',
        showCancelButton: true,
        confirmButtonText: "Add to Cart",
        cancelButtonText: "Cancel",
        reverseButtons: true,
        inputValidator: (result) => {
          if (!result) {
            return "Enter quantity!";
          } else {
          }
          var formattedNumber = ("0" + result).slice(-2);
          if (formattedNumber < data_4) {
          } else if (formattedNumber <= data_4) {
          } else {
            return "stocks not enough!";
          }
        },
        inputAttributes: {
        maxlength: "10"
      }
      }).then((result) => {
        if (result.isConfirmed) {
          const inputOptions = [];
              inputOptions[data_3]= data_3;
              inputOptions[data_5]= data_5;
              inputOptions[data_6]= data_6;
              swalWithBootstrapButtons.fire({
                title: "SELECT PRICE",
                input: "radio",
                inputOptions,
                inputValidator: (value) => {
                  if (!value) {
                    return value;
                  }
                },
                showCancelButton: true,
                confirmButtonText: "Confirm",
                cancelButtonText: "Cancel",
                reverseButtons: true
            }).then((result2) => {
              if (result2.isConfirmed) {
                window.location.href = window.location.href+'&price='+result2.value+'&product_id='+data_2+'&quantity='+result.value;
              } else {
                result.dismiss === Swal.DismissReason.cancel
              }
            });
          
        } else {
          result.dismiss === Swal.DismissReason.cancel
        }
      });
    }
    function btn_tr(data_1,data_2,data_3,data_4,data_5,data_6) {
      const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
          confirmButton: "btn btn-success",
          cancelButton: "btn btn-secondary me-1"
        },
        buttonsStyling: false
      });
      swalWithBootstrapButtons.fire({
        title: "Create New Transaction?",
        showCancelButton: true,
        confirmButtonText: "Create",
        cancelButtonText: "Cancel",
        reverseButtons: true
      }).then((result1) => {
        if (result1.isConfirmed) {
            swalWithBootstrapButtons.fire({
              title: data_1,
              input: "number",
              footer: '<h6 class="text-white text-center">Available stocks: '+data_4+'</h6>',
              showCancelButton: true,
              confirmButtonText: "Add to Cart",
              cancelButtonText: "Cancel",
              reverseButtons: true,
              inputValidator: (result2) => {
                if (!result2) {
                  return "Enter quantity!";
                } else {
                }
                var formattedNumber = ("0" + result2).slice(-2);
                if (formattedNumber < data_4) {
                } else if (formattedNumber <= data_4) {
                } else {
                  return "stocks not enough!";
                }
              },
              inputAttributes: {
              maxlength: "10"
            }
            }).then((result2) => {
              /////////////////////
          
              const inputOptions = [];
              inputOptions[data_3]= data_3;
              inputOptions[data_5]= data_5;
              inputOptions[data_6]= data_6;
              swalWithBootstrapButtons.fire({
                title: "SELECT PRICE",
                input: "radio",
                inputOptions,
                inputValidator: (value) => {
                  if (!value) {
                    return value;
                  }
                },
                showCancelButton: true,
                confirmButtonText: "Confirm",
                cancelButtonText: "Cancel",
                reverseButtons: true
            }).then((result3) => {
              if (result3.isConfirmed) {
                window.location.href = window.location.href+'&price='+result3.value+'&product_id='+data_2+'&quantity='+result2.value+'&id=<?php echo $TR;?>';
              } else {
                result.dismiss === Swal.DismissReason.cancel
              }
            });
              /////////////////////
            });
        } else {
          result.dismiss === Swal.DismissReason.cancel
        }
      });
    }
    function btn_cash() {
      const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
          confirmButton: "btn btn-success",
          cancelButton: "btn btn-secondary me-1"
        },
        buttonsStyling: false
      });
      swalWithBootstrapButtons.fire({
        title: "<?php echo "Cash to pay : P " . $total ."";?>",
        input: "number",
        showCancelButton: true,
        confirmButtonText: "Pay",
        cancelButtonText: "Cancel",
        reverseButtons: true,
        inputValidator: (result) => {
          if (!result) {
            return "Enter payment!";
          } else {
          }
          if (result > <?php echo $total;?>) {
          } else if (result >= <?php echo $total;?>) {
          } else {
            return "Payment not enough!";
          }
        }
      }).then((result) => {
  
        change = result.value - <?php echo $total ;?>;
        if (result.isConfirmed) {
          swalWithBootstrapButtons.fire({
            title: "Change is P "+change.toFixed(2),
            icon: "success",
            showConfirmButton: false,
            timer: 1500
          }).then((result2) => {
            if (result2.dismiss === swalWithBootstrapButtons.DismissReason.timer) {
              window.location.href = window.location.href+'&payment='+result.value+'&payment1=CASH';
            }
           });
        } else {
          result.dismiss === Swal.DismissReason.cancel
        }
      });
    }
    function btn_other() {
      const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
          confirmButton: "btn btn-success",
          cancelButton: "btn btn-secondary me-1"
        },
        buttonsStyling: false
      });
      swalWithBootstrapButtons.fire({
        title: "SELECT PAYMENT",
        input: "select",
        inputOptions: {
        OPTIONS : {
          <?php while($row2c = mysqli_fetch_array($result2c)):;?>
          <?php echo $row2c['options'];?>: "<?php echo $row2c['options'];?>",
          <?php endwhile; ?>
          }
        },
        showCancelButton: true,
        confirmButtonText: "Received, Confirm",
        cancelButtonText: "Cancel",
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          swalWithBootstrapButtons.fire({
            title: "No Change! Confirm on",
            icon: "success",
            showConfirmButton: false,
            timer: 1500
          }).then((result2) => {
            if (result2.dismiss === swalWithBootstrapButtons.DismissReason.timer) {
              window.location.href = window.location.href+'&payment=<?php echo $total ;?>&payment1='+result.value;
            }
           });
        } else {
          result.dismiss === Swal.DismissReason.cancel
        }
      });
    }
    function btn_disc() { 
      const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
          confirmButton: "btn btn-success",
          cancelButton: "btn btn-secondary me-1"
        },
        buttonsStyling: false
      });
      swalWithBootstrapButtons.fire({
        title: "SELECT DISCOUNT",
        input: "select",
        inputOptions: {
        VOUCHERS : {
          <?php while($row2b = mysqli_fetch_array($result2b)):;?>
          <?php echo $row2b['id'];?>: "<?php echo $row2b['description'];?>",
          <?php endwhile; ?>
          }
        },
        showCancelButton: true,
        confirmButtonText: "Confirm",
        cancelButtonText: "Cancel",
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = window.location.href+'&disc='+result.value;
        } else {
          result.dismiss === Swal.DismissReason.cancel
        }
      });
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
            <?php while($row1 = mysqli_fetch_array($result1)):;?>
            "<?php echo $row1['specification'];?>",
            <?php endwhile; ?>
          ];
          var category = [
            <?php while($row3 = mysqli_fetch_array($result3)):;?>
            "<?php echo $row3['category'];?>",
            <?php endwhile; ?>
          ];
          var pb = [
            <?php while($row4 = mysqli_fetch_array($result4)):;?>
            "<?php echo $row4['product_brand'];?>",
            <?php endwhile; ?>
          ];

          autocomplete(document.getElementById("search"), category.concat(specification, pb));
   
    
  </script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/feather-icons@4.28.0/dist/feather.min.js" integrity="sha384-uO3SXW5IuS1ZpFPKugNNWqTZRRglnUJK6UAZ/gxOX80nxEkN9NcGZTftn6RzhGWE" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</script><script src="dashboard.js"></script>
<script src="assets/js/jquery.js"></script>
  </body>
</html>