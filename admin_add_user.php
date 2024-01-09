<?php
include('user_session.php');
$sql3 = "SELECT * FROM `tb_mc_brand`";
$result3=mysqli_query($db,$sql3);
if(!empty($_GET['Xid'])) {
  $sql7="DELETE FROM tb_users WHERE tb_users.userid='" .$_GET['Xid']. "'";
  $sql8="DELETE FROM tb_accounts WHERE tb_accounts.userid='" .$_GET['Xid']. "'";

  if (($db->query($sql7)) && ($db->query($sql8))=== TRUE) {
    echo "User Deleted";
    header("Location: admin_add_user.php");
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
  if (empty($_POST["type"])) {
    $type_err = "* ";
  } else {
    $type = validateInput($_POST["type"]);
  }
  if (empty($_POST["userid"])) {
    $userid_err = "* ";
  } else {
    $userid = validateInput($_POST["userid"]);
  }
  if (empty($_POST["first_name"])) {
    $first_name_err = "* ";
  } else {
    $first_name = validateInput($_POST["first_name"]);
  }
  if (empty($_POST["middle_name"])) {
    $middle_name_err = "* ";
  } else {
    $middle_name = validateInput($_POST["middle_name"]);
  }
  if (empty($_POST["last_name"])) {
    $last_name = "* ";
  } else {
    $last_name = validateInput($_POST["last_name"]);
  }
  if (empty($_POST["phone"])) {
    $phone_err = "* ";
  } else {
    $phone = validateInput($_POST["phone"]);
  }
  if(!empty($_POST["userid"]) && !empty($_POST["first_name"] ) && !empty($_POST["middle_name"]) && !empty($_POST["last_name"]) && !empty($_POST["phone"]) && !empty($_POST["type"]))
  {
    try {

        $sql2 = "INSERT INTO `tb_users` (`userid`, `first_name`, `middle_name`, `last_name`, `phone`)
        VALUES ('$userid','$first_name','$middle_name','$last_name','$phone')";
        $saveCashier = mysqli_query($db, $sql2);
        $sql3 = "INSERT INTO `tb_accounts` (`userid`, `password`, `acc_type`)
        VALUES ('$userid','pw123','$type')";
        $saveAcc = mysqli_query($db, $sql3);
        
        header("Location: admin_add_user.php");
      
    }
    catch(PDOException $e)
      {
        echo $sql2 . "<br>" . $e->getMessage();
      }
    $db=null;
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
    <title>ADD USER</title>
 
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
            <a class="nav-link" href="admin_add_supplier.php">
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
            <a class="nav-link active" href="admin_add_user.php">
              <span data-feather="file-text" class="align-text-bottom"></span>
              Add new user
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
    <h6 class="text-center mb-3 mt-5">Add New User</h6>
      <div class=" align-items-center ">
      <form method="post" action="" enctype="multipart/form-data">
      <div class="row mb-3">
            <div class="col-md">
              <div class="form-floating shadow">
                <select name="type" class="form-select" aria-label="Example select with button addon">
                  <option class="dropdown-item" value="ADMIN">ADMIN</option>
                  <option class="dropdown-item" value="CASHIER">CASHIER</option>
                </select>
                <label for="floatingInputGrid">ACCOUNT TYPE</label>
              </div>
            </div>
      </div>
      <div class="row mb-3">
            <div class="col-md">
              <div class="form-floating shadow">
                <input type="text" name="userid" class="form-control" placeholder="User #"   aria-describedby="button-addon2" required>
                <label for="floatingInputGrid">USER ID</label>
              </div>
            </div>
            <div class="col-md">
              <div class="form-floating shadow">
              <input type="text" name="phone" class="form-control" placeholder="Phone Number"  aria-describedby="button-addon2" required>
                <label for="floatingInputGrid">MOBILE NUMBER</label>
              </div>
            </div>
      </div>
      <div class="row mb-3">
            <div class="col-md">
              <div class="form-floating shadow">
              <input type="text" name="first_name" class="form-control" placeholder="First Name" onkeyup="this.value = this.value.toUpperCase();"  aria-describedby="button-addon2" required>
                <label for="floatingInputGrid">FIRST NAME</label>
              </div>
            </div>
            <div class="col-md">
              <div class="form-floating shadow">
              <input type="text" name="middle_name" class="form-control" placeholder="Middle Name" onkeyup="this.value = this.value.toUpperCase();" aria-describedby="button-addon2" required>
                <label for="floatingInputGrid">MIDDLE NAME</label>
              </div>
            </div>
            <div class="col-md">
              <div class="form-floating shadow">
              <input type="text" name="last_name" class="form-control" placeholder="Last Name" onkeyup="this.value = this.value.toUpperCase();" aria-describedby="button-addon2" required>
                <label for="floatingInputGrid">LAST NAME</label>
              </div>
            </div>
      </div>
      <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <button class="btn btn-success" type="submit">SAVE <span data-feather="upload-cloud" class="align-text-end"></button>
        </div>
    
      </form>
      </div>
      <div class="table" id="page">
      <h6 class="mt-3 text-muted text-center">Recently added users</h6>
        <table class="table table-hover table-sm">
          <thead>
            <tr class="text-muted">
              <th scope="col">User #</th>
              <th scope="col">Fullname</th>
              <th scope="col"></th>
            </tr>
          </thead>
          <tbody>
            <?php
                $sql="SELECT
                tb_users.userid,
                CONCAT(tb_users.first_name,' ',tb_users.middle_name,' ',tb_users.last_name) AS name
                FROM tb_users";
                                                                                    
                $result = mysqli_query($db,$sql);

                if (mysqli_num_rows($result) > 0) 
                {
                foreach($result as $items)
                {
            ?>
            <tr>
                <td><?php echo "".$items['userid'].""; ?></td>
                <td><?php echo "".$items['name'].""; ?></td>
                <td>
        
                  <button type="button" class="btn btn-sm p-0 m-0" data-bs-toggle="modal" data-bs-target="#exampleModal">
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
                          <h6 class="modal-title" id="exampleModalLabel">Delete User Account</h6>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button onclick="location.href='admin_add_user.php?Xid=<?php echo $items['userid'];?>'" type="button" class="btn btn-sm btn-danger">Delete</button>
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
