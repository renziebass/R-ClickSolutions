<?php
include('user_session.php');
if(!empty($_GET['xid'])) {
  $sql7="DELETE FROM tb_supplier WHERE tb_supplier.id='" .$_GET['xid']. "'";

  if (($db->query($sql7)) === TRUE) {
    echo "Supplier Deleted";
    header("Location: admin_add_supplier.php");
  } else {
    echo "Error updating record: " . $db->error;
  }
}
function validateInput($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
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
        
        header("Location: admin_add_supplier.php");
      
    }
    catch(PDOException $e)
      {
        echo $sql2 . "<br>" . $e->getMessage();
      }
    $db=null;
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
    <title>ADD SUPPLIER</title>
 
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
            <a class="nav-link" href="admin_add_products.php">
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
            <a class="nav-link active" href="admin_add_supplier.php">
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
          <li class="nav-item">
            <a class="nav-link" href="admin_add_discount.php">
              <span data-feather="file-text" class="align-text-bottom"></span>
              Add new discounts
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
    <h6 class="text-center mb-3 mt-5">Add New Supplier</h6>
      <div class=" align-items-center ">
      <form method="post" action="" enctype="multipart/form-data">
        <div class="row mb-3">
            <div class="col-md">
              <div class="form-floating shadow">
                <input type="text" name="id" class="form-control" placeholder="ID" aria-label="Recipient's username" aria-describedby="button-addon2" required>
                <label for="floatingInputGrid">ID</label>
              </div>
            </div>
            <div class="col-md">
              <div class="form-floating shadow">
              <input onkeyup="this.value = this.value.toUpperCase();" type="text" name="name" class="form-control" placeholder="Company/Name" aria-label="Recipient's username" aria-describedby="button-addon2" required>
              <label for="floatingInputGrid">SUPPLIER NAME</label>
            </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md">
              <div class="form-floating shadow">
              <input onkeyup="this.value = this.value.toUpperCase();" type="text" name="address" class="form-control" placeholder="Location" aria-label="Recipient's username" aria-describedby="button-addon2" required>
                <label for="floatingInputGrid">ADDRESS</label>
              </div>
            </div>
            <div class="col-md">
              <div class="form-floating shadow">
              <input type="text" name="number" class="form-control" placeholder="Mobile" aria-label="Recipient's username" aria-describedby="button-addon2" required>
              <label for="floatingInputGrid">CONTACT NUMBER</label>
            </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md">
              <div class="form-floating shadow">
              <input onkeyup="this.value = this.value.toUpperCase();" type="text" name="notes" class="form-control" placeholder="Notes" aria-label="Recipient's username" aria-describedby="button-addon2" required>
                <label for="floatingInputGrid">NOTES</label>
              </div>
            </div>
        </div>
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <button class="btn btn-success" type="submit">SAVE <span data-feather="upload-cloud" class="align-text-end"></button>
        </div>
      </form>
      </div>
      <div class="table" id="page">
      <h6 class="mt-3 text-muted text-center">Recently added suppliers</h6>
        <table class="table table-hover table-sm">
          <thead>
          <tr class="text-muted">
              <th scope="col">Supplier</th>
              <th scope="col">Specification</th>
              <th scope="col">Address</th>
              <th scope="col">Notes</th>
              <th scope="col">Phone</th>
              <th scope="col"></th>
            </tr>
          </thead>
          <tbody>
            <?php
                $sql="SELECT * FROM tb_supplier";
                                                                                    
                $result = mysqli_query($db,$sql);

                if (mysqli_num_rows($result) > 0) 
                {
                foreach($result as $items)
                {
            ?>
            <tr>
                <td><?php echo $items['id']; ?></td>
                <td><?php echo $items['name']; ?></td>
                <td><?php echo $items['address']; ?></td>
                <td><?php echo $items['notes']; ?></td>
                <td><?php echo $items['mobile']; ?></td>
                <td>
        
                  <button type="button" class="btn btn-sm p-0 m-0" data-bs-toggle="modal" data-bs-target="#exampleModal" data-bs1="<?php echo $items['id']; ?>" data-bs2="<?php echo $items['name']; ?>">
                    <span>
                      <svg  class="text-danger" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-square-fill" viewBox="0 0 16 16">
                      <path d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm3.354 4.646L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 1 1 .708-.708z"/>
                      </svg>
                    </span>
                  </button>

                  <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h6 class="modal-title" id="exampleModalLabel"></h6>
                        </div>
                        <div class="modal-body">
                            <form method="get" enctype="multipart/form-data">
                              <div class="mb-3">
                        
                                <input type="hidden" id="xid" name="xid" class="form-control">
                              
                              </div>
                          </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                        </div>
                      </div>
                    </div>
                  </div>
                </td>
           
            </tr>
            <?php
            } 
            } 
            ?>
          </tbody>
          <script>
            const exampleModal = document.getElementById('exampleModal')
            if (exampleModal) {
              exampleModal.addEventListener('show.bs.modal', event => {
                // Button that triggered the modal
                const button = event.relatedTarget
                // Extract info from data-bs-* attributes
                const recipient = button.getAttribute('data-bs1')
                const recipient2 = button.getAttribute('data-bs2')
              
                // If necessary, you could initiate an Ajax request here
                // and then do the updating in a callback.

                // Update the modal's content.
         
                const modalBodyInput1 = document.getElementById('xid')
                const modalTitle = exampleModal.querySelector('.modal-title')
            
                modalBodyInput1.value = recipient
                modalTitle.textContent = `Delete Supplier ${recipient2}`

              })
            }
          </script>
        </table>
      </div>
    </main>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/feather-icons@4.28.0/dist/feather.min.js" integrity="sha384-uO3SXW5IuS1ZpFPKugNNWqTZRRglnUJK6UAZ/gxOX80nxEkN9NcGZTftn6RzhGWE" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</script><script src="dashboard.js"></script>

  </body>
</html>
