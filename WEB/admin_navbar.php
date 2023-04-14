<nav class="navbar navbar-expand-lg navbar-light mx-5">
          <a class="navbar-brand font-weight-bold" href="#">R-Click POS</a>
          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse d-flex flex-row-reverse" id="navbarNavDropdown">
            <ul class="navbar-nav">
              <li class="nav-item active">
                <a class="nav-link font-weight-bold" href="admin_home.php">Dashboard<span class="sr-only">(current)</span></a>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle font-weight-bold" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
                  Transactions
                </a>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="admin_recent.php">Recent</a>
                  <a class="dropdown-item" href="admin_paid.php?date=<?php echo date("Y-m-d") ?>">Paid</a>
                  <a class="dropdown-item" href="admin_unpaid.php?date=<?php echo date("Y-m-d") ?>">Unpaid</a>
                </div>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle font-weight-bold" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
                  Products
                </a>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="admin_category.php?pcategory=BRAKE CABLE">Manage</a>
                  <a class="dropdown-item" href="admin_inventory_report.php">Report</a>
                  <a class="dropdown-item" href="admin_addcategory.php">Add Category</a>
                  <a class="dropdown-item" href="admin_addproduct.php">Add Product</a>
                  <a class="dropdown-item" href="admin_restock_product.php">Re-Stock Product</a>
                </div>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle font-weight-bold" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
                  Supplier
                </a>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="admin_supplier.php">Manage</a>
                 
                </div>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle font-weight-bold" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
                  Users
                </a>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="admin_manage_cashiers.php">Manage Cashiers</a>
                  <a class="dropdown-item" href="admin_manage_admins.php">Manage Admin</a>
                </div>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle font-weight-bold" href="#" role="button" data-toggle="dropdown" aria-expanded="false">
                  Account
                </a>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="#">Setting</a>
                  <a class="dropdown-item" href="logout.php">Logout</a>
                </div>
              </li>
            </ul>
          </div>
</nav>