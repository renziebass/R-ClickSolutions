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

          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span data-feather="shopping-cart" class="align-text-bottom"></span>
              Products
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
              <li><a class="dropdown-item" href="admin_inventory.php">Inventory</a></li>
              <li><a class="dropdown-item" href="admin_products.php">All Products</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item" href="admin_product_restock.php?search=a">Re-Stock Product</a></li>
              <li><a class="dropdown-item" href="admin_add_products.php">Add Product</a></li>
              <li><a class="dropdown-item" href="admin_add_category.php">Add Category</a></li>
              <li><a class="dropdown-item" href="admin_add_supplier.php">Add Supplier</a></li>
              <li><a class="dropdown-item" href="admin_add_mc.php">Add Brand/Model</a></li>
            </ul>
          </li>

          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span data-feather="users" class="align-text-bottom"></span>
              Users
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
              <li><a class="dropdown-item" href="admin_users.php">All Users</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item" href="admin_add_user.php">Add User</a></li>
              
            </ul>
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
            <a class="nav-link" href="admin_add_discount.php">
              <span data-feather="file-text" class="align-text-bottom"></span>
              Add Discounts
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="admin_add_payment.php">
              <span data-feather="file-text" class="align-text-bottom"></span>
              Add Payment Method
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