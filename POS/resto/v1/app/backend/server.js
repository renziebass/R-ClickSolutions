const express = require('express');
const mysql = require('mysql');
const cors = require('cors');
require('dotenv').config();

const app = express();
app.use(cors());


app.use(express.json());

const db = mysql.createConnection({
  host: process.env.DB_HOST,       // remote server IP or domain
  user: process.env.DB_USER,
  password: process.env.DB_PASS,
  database: process.env.DB_NAME,
  port: process.env.DB_PORT
});

db.connect(err => {
  if (err) {
    console.error('DB connection error:', err);
    return;
  }
  console.log('Connected to remote MySQL DB');
});

setInterval(() => {
    db.query('SELECT 1');
  }, 5000);


// Update the login route to return user data
app.post('/api/login', (req, res) => {
  const { id, password } = req.body;

  // Modified query to select specific user data
  const query = 'SELECT id, name, role FROM users WHERE id = ? AND password_hash = ?';
  db.query(query, [id, password], (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({ success: false, message: 'Database error' });
    }

    if (results.length > 0) {
      // Return user data along with success message
      const userData = results[0];
      res.json({
        success: true,
        message: 'Login successful',
        user: {
          id: userData.id,
          name: userData.name,
          role: userData.role
        }
      });
    } else {
      res.status(401).json({ success: false, message: 'Invalid credentials' });
    }
  });
});

//fetch orders (admin)
app.get('/api/fetch-admin-orders', (req, res) => {
  const query = `
    SELECT
    orders.id, orders.table_id,
    orders.type, orders.status,
    orders.total_amount, orders.discount_total,
    orders.created_by, orders.created_at,
    orders.completed_at,
    orders.order_number, payments.id,
    payments.order_id, payments.amount,
    payments.method, payments.paid_at, payments.received_by
    FROM orders
    LEFT JOIN payments ON orders.id=payments.order_id
    GROUP BY orders.id
  `;
  
  db.query(query, (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({ 
        success: false, 
        message: 'Error fetching orders' 
      });
    }
    res.json({
      success: true,
      data: results
    });
  });
});

//fetch menu items
app.get('/api/menu-items', (req, res) => {
  const query = `
    SELECT * FROM menu_items
  `;
  
  db.query(query, (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({ 
        success: false, 
        message: 'Error fetching menu items' 
      });
    }
    res.json({
      success: true,
      data: results
    });
  });
});

//fetch category
app.get('/api/categories', (req, res) => {
  const query = `
    SELECT * FROM categories
  `;
  
  db.query(query, (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({ 
        success: false, 
        message: 'Error fetching categories'
      });
    }
    res.json({
      success: true,
      data: results
    });
  });
});

//fetch discounts
app.get('/api/discounts', (req, res) => {
  const query = `
    SELECT * FROM discounts
  `;
  
  db.query(query, (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({ 
        success: false, 
        message: 'Error fetching discounts'
      });
    }
    res.json({
      success: true,
      data: results
    });
  });
});

//fetch tables
app.get('/api/fetch-tables', (req, res) => {
  const query = `
    SELECT * FROM tables WHERE tables.is_available=1
  `;
  
  db.query(query, (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({ 
        success: false, 
        message: 'Error fetching tables'
      });
    }
    res.json({
      success: true,
      data: results
    });
  });
});

//fetch pending orders
app.get('/api/fetch-pending-orders', (req, res) => {
  const query = `
  SELECT DISTINCT
    orders.id AS order_id,
    orders.type AS order_type,
    CASE 
        WHEN orders.type = 'dine-in' THEN CONCAT('table#', orders.table_id)
        ELSE orders.id
    END AS identifier
FROM orders
JOIN order_items
    ON order_items.order_id = orders.id
WHERE orders.status != 'paid'
OR order_items.status IN ('preparing','ready')
`;

db.query(query, (err, results) => {
  if (err) {
    console.error('Database error:', err);
    return res.status(500).json({ 
      success: false, 
      message: 'Error fetching categories'
    });
  }
  res.json({
    success: true,
    data: results
  });
});
});

// Create new menu item
app.post('/api/menu-items', (req, res) => {
  const { 
    name, 
    description, 
    category, 
    price, 
    capital_price 
  } = req.body;

  // Validation
  if (!name || !price || !capital_price || !category) {
    return res.status(400).json({
      success: false,
      message: 'Name, category, price and capital price are required'
    });
  }

  const query = `
    INSERT INTO menu_items 
    (name, description, category, price, capital_price) 
    VALUES (?, ?, ?, ?, ?)
  `;

  db.query(
    query, 
    [name.trim(), description, category, price, capital_price],
    (err, result) => {
      if (err) {
        console.error('Database error:', err);
        return res.status(500).json({
          success: false,
          message: 'Error creating menu item'
        });
      }

      res.status(201).json({
        success: true,
        message: 'Menu item created successfully',
        data: {
          id: result.insertId,
          name,
          description,
          category,
          price,
          capital_price
        }
      });
    }
  );
});

// Update menu item
app.post('/api/update-menu-items', (req, res) => {
  const { 
    id,
    name, 
    description, 
    category, 
    price, 
    capital_price 
  } = req.body;

  // Validation
  if (!name || !price || !capital_price || !category) {
    return res.status(400).json({
      success: false,
      message: 'Name, category, price and capital price are required'
    });
  }

  const query = `
    UPDATE menu_items 
    SET name = ?, description = ?, category = ?, price = ?, capital_price = ?
    WHERE id = ?
  `;

  db.query(
    query, 
    [name.trim(), description, category, price, capital_price, id],
    (err, result) => {
      if (err) {
        console.error('Database error:', err);
        return res.status(500).json({
          success: false,
          message: 'Error udpate menu item'
        });
      }

      res.status(201).json({
        success: true,
        message: 'Menu item update successfully',
        data: {
          id: result.insertId,
          name,
          description,
          category,
          price,
          capital_price
        }
      });
    }
  );
});

// Create new menu category
app.post('/api/menu-category', (req, res) => {
  const { 
    category, 
    description
  } = req.body;

  // Validation
  if (!category) {
    return res.status(400).json({
      success: false,
      message: 'category is required'
    });
  }

  const query = `
    INSERT INTO categories
    (name, description)
    VALUES (?, ?)
  `;

  db.query(
    query, 
    [category.trim(), description],
    (err, result) => {
      if (err) {
        console.error('Database error:', err);
        return res.status(500).json({
          success: false,
          message: 'Error creating category'
        });
      }

      res.status(201).json({
        success: true,
        message: 'Category created successfully',
        data: {
          id: result.insertId,
          category,
          description
        }
      });
    }
  );
});

// Create new discount
app.post('/api/add-discount', (req, res) => {
  const { 
    name, 
    type,
    value,
    scope
  } = req.body;

  // Validation
  if (!name || !type || !value || !scope) {
    return res.status(400).json({
      success: false,
      message: 'All fields are required'
    });
  }

  const query = `
    INSERT INTO discounts
    (name, type, value, scope)
    VALUES (?, ?, ?, ?)
  `;

  db.query(
    query, 
    [name.trim(), type.trim(), value.trim(), scope.trim()],
    (err, result) => {
      if (err) {
        console.error('Database error:', err);
        return res.status(500).json({
          success: false,
          message: 'Error creating discount'
        });
      }

      res.status(201).json({
        success: true,
        message: 'Discount created successfully',
        data: {
          id: result.insertId,
          name,
          type,
          value,
          scope
        }
      });
    }
  );
});

// Update menu item
app.put('/api/menu-items/:id', (req, res) => {
  const { id } = req.params;
  const { 
    name, 
    description, 
    category, 
    price, 
    capital_price,
    is_available 
  } = req.body;

  const query = `
    UPDATE menu_items 
    SET name = ?, 
        description = ?, 
        category = ?, 
        price = ?, 
        capital_price = ?,
        is_available = ?
    WHERE id = ?
  `;

  db.query(
    query,
    [name, description, category, price, capital_price, is_available, id],
    (err, result) => {
      if (err) {
        console.error('Database error:', err);
        return res.status(500).json({
          success: false,
          message: 'Error updating menu item'
        });
      }

      if (result.affectedRows === 0) {
        return res.status(404).json({
          success: false,
          message: 'Menu item not found'
        });
      }

      res.json({
        success: true,
        message: 'Menu item updated successfully'
      });
    }
  );
});

//fetch users
app.get('/api/users', (req, res) => {
  const query = `
    SELECT * FROM users
  `;
  
  db.query(query, (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({ 
        success: false, 
        message: 'Error fetching menu items' 
      });
    }
    res.json({
      success: true,
      data: results
    });
  });
});

//fetch min/max payment dates
app.get('/api/fetch-dates', (req, res) => {
  const query = `
    SELECT 
  DATE(MIN(paid_at)) AS date1, 
  DATE(MAX(paid_at)) AS date2 
FROM payments
  `;
  
  db.query(query, (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({ 
        success: false, 
        message: 'Error fetching dates' 
      });
    }
    res.json({
      success: true,
      data: results
    });
  });
});

//fetch sales data 1
app.get('/api/fetch-sales-data1', (req, res) => {
  const query = `
    SELECT
    -- Order counts by type
    SUM(CASE WHEN type = 'dine-in' THEN 1 ELSE 0 END) AS dine_in,
    SUM(CASE WHEN type = 'take-out' THEN 1 ELSE 0 END) AS take_out,
    SUM(CASE WHEN type = 'delivery' THEN 1 ELSE 0 END) AS delivery,
    
    -- Total number of orders
    COUNT(*) AS total_orders,
    
    -- Sales calculations
    SUM(total_amount) AS gross_sales,
    SUM(discount_total) AS total_discounts,
    SUM(total_amount - discount_total) AS net_sales
FROM 
    orders
WHERE 
    status = 'paid'
    AND DATE(created_at) = CURDATE()
  `;

  db.query(query, (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({ 
        success: false, 
        message: 'Error fetching sales data' 
      });
    }
    res.json({
      success: true,
      data: results
    });
  });
});

//fetch top selling item
app.get('/api/fetch-top-selling-item', (req, res) => {
  const query = `
    SELECT 
    mi.id,
    mi.name,
    SUM(oi.quantity) AS total_sold,
    SUM(oi.price * oi.quantity) AS gross_sales
FROM 
    orders o
JOIN 
    order_items oi ON o.id = oi.order_id
JOIN 
    menu_items mi ON oi.menu_item_id = mi.id
WHERE 
    o.status = 'paid'
    AND DATE(o.created_at) = CURDATE()
GROUP BY 
    mi.id, mi.name
ORDER BY 
    total_sold DESC
LIMIT 1
  `;

  db.query(query, (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({ 
        success: false, 
        message: 'Error fetching data' 
      });
    }
    res.json({
      success: true,
      data: results
    });
  });
});

//fetch pending numbers
app.get('/api/fetch-pending-numbers', (req, res) => {
  const query = `
    SELECT 
    COUNT(*) AS pending_orders
FROM 
    orders
WHERE 
    status = 'pending'
  `;

  db.query(query, (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({ 
        success: false, 
        message: 'Error fetching data' 
      });
    }
    res.json({
      success: true,
      data: results
    });
  });
});

//fetch menu items summary
app.get('/api/fetch-menu-items-summary', (req, res) => {
  const query = `
 SELECT 
    mi.name AS menu_item,
    SUM(oi.quantity) AS total_quantity,
    SUM(oi.quantity * mi.price) AS total_revenue
FROM 
    orders o
JOIN 
    order_items oi ON o.id = oi.order_id
JOIN 
    menu_items mi ON oi.menu_item_id = mi.id
WHERE 
    o.status = 'paid'
    AND DATE(o.created_at) = CURDATE()
GROUP BY 
    mi.id, mi.name
ORDER BY 
    total_quantity DESC
  `;

  db.query(query, (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({ 
        success: false, 
        message: 'Error fetching data' 
      });
    }
    res.json({
      success: true,
      data: results
    });
  });
});

//fetch menu items summary1
app.get('/api/fetch-menu-items-summary1', (req, res) => {
  const { date1, date2 } = req.query;
  const query = `
 SELECT 
    mi.name AS menu_item,
    SUM(oi.quantity) AS total_quantity,
    SUM(mi.capital_price) AS capital_total,
    SUM(oi.quantity * mi.price) AS total_revenue,
    SUM(oi.price * oi.quantity) - SUM(mi.capital_price * oi.quantity) AS gross_profit
FROM 
    orders o
JOIN 
    order_items oi ON o.id = oi.order_id
JOIN 
    menu_items mi ON oi.menu_item_id = mi.id
WHERE 
    o.status = 'paid'
    AND DATE(o.created_at) BETWEEN ? AND ?
    AND mi.category NOT IN ('BEVERAGES', 'DESSERTS')
GROUP BY 
    mi.id, mi.name
ORDER BY 
    total_quantity DESC;
  `;

  db.query(query, [date1, date2], (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({ 
        success: false, 
        message: 'Error fetching data' 
      });
    }
    res.json({
      success: true,
      data: results
    });
  });
});

//fetch menu items summary2
app.get('/api/fetch-menu-items-summary2', (req, res) => {
  const { date1, date2 } = req.query;
  const query = `
 SELECT 
    mi.name AS menu_item,
    SUM(oi.quantity) AS total_quantity,
    SUM(mi.capital_price) AS capital_total,
    SUM(oi.quantity * mi.price) AS total_revenue,
    SUM(oi.price * oi.quantity) - SUM(mi.capital_price * oi.quantity) AS gross_profit
FROM 
    orders o
JOIN 
    order_items oi ON o.id = oi.order_id
JOIN 
    menu_items mi ON oi.menu_item_id = mi.id
WHERE 
    o.status = 'paid'
    AND DATE(o.created_at) BETWEEN ? AND ?
    AND mi.category IN ('BEVERAGES', 'DESSERTS')
GROUP BY 
    mi.id, mi.name
ORDER BY 
    total_quantity DESC;
  `;

  db.query(query, [date1, date2], (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({ 
        success: false, 
        message: 'Error fetching data' 
      });
    }
    res.json({
      success: true,
      data: results
    });
  });
});

//fetch admin kitchen queue
app.get('/api/fetch-kitchen-queue', (req, res) => {
  const query = `
    SELECT 
    mi.name AS menu_item_name,
    oi.quantity,
    oi.updated_at,
    CASE 
        WHEN o.type = 'dine-in' THEN CONCAT('table ', o.table_id)
        WHEN o.type = 'take-out' THEN CONCAT('take-out ', o.id)
        ELSE CONCAT('delivery ', o.id)
    END AS identifier
FROM 
    orders o
JOIN 
    order_items oi ON o.id = oi.order_id
JOIN 
    menu_items mi ON oi.menu_item_id = mi.id
WHERE 
    oi.status NOT IN ('ready', 'served', 'cancelled')
    AND mi.category NOT IN ('BEVERAGES', 'DESSERTS')
ORDER BY 
    oi.updated_at ASC
  `;

  db.query(query, (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({ 
        success: false, 
        message: 'Error fetching data' 
      });
    }
    res.json({
      success: true,
      data: results
    });
  });
});

//fetch admin drinks queue
app.get('/api/fetch-drinks-queue', (req, res) => {
  const query = `
    SELECT 
    mi.name AS menu_item_name,
    oi.quantity,
    oi.updated_at,
    CASE 
        WHEN o.type = 'dine-in' THEN CONCAT('table ', o.table_id)
        WHEN o.type = 'take-out' THEN CONCAT('take-out ', o.id)
        ELSE CONCAT('delivery ', o.id)
    END AS identifier
FROM 
    orders o
JOIN 
    order_items oi ON o.id = oi.order_id
JOIN 
    menu_items mi ON oi.menu_item_id = mi.id
WHERE 
    oi.status NOT IN ('ready', 'served', 'cancelled')
    AND mi.category = 'BEVERAGES'
ORDER BY 
    oi.updated_at ASC
  `;

  db.query(query, (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({ 
        success: false, 
        message: 'Error fetching data' 
      });
    }
    res.json({
      success: true,
      data: results
    });
  });
});

// Create new user
app.post('/api/users', (req, res) => {
  const { 
    name, 
    email, 
    password_hash, 
    role
  } = req.body;

  // Validation
  if (!name || !email || !password_hash || !role) {
    return res.status(400).json({
      success: false,
      message: 'Name, email, password and role are required'
    });
  }

  const query = `
    INSERT INTO users 
    (name, email, password_hash, role) 
    VALUES (?, ?, ?, ?)
  `;

  db.query(
    query, 
    [name.trim(), email.trim(), password_hash, role],
    (err, result) => {
      if (err) {
        console.error('Database error:', err);
        return res.status(500).json({
          success: false,
          message: 'Error creating menu item'
        });
      }

      res.status(201).json({
        success: true,
        message: 'user created successfully',
        data: {
          id: result.insertId,
          name,
          email,
          password_hash,
          role
        }
      });
    }
  );
});

// update user
app.post('/api/update-users', (req, res) => {
  const { 
    id,
    name, 
    email, 
    password_hash, 
    role
  } = req.body;

  // Validation
  if (!id || !name || !email || !password_hash || !role) {
    return res.status(400).json({
      success: false,
      message: 'Name, email, password and role are required'
    });
  }

  const query = `
    UPDATE users 
    SET name = ?, email = ?, password_hash = ?, role = ?
    WHERE id = ?
  `;

  db.query(
    query, 
    [name.trim(), email.trim(), password_hash, role, id],
    (err, result) => {
      if (err) {
        console.error('Database error:', err);
        return res.status(500).json({
          success: false,
          message: 'Error updating user'
        });
      }

      res.status(201).json({
        success: true,
        message: 'user updated successfully',
        data: {
          id: result.insertId,
          name,
          email,
          password_hash,
          role
        }
      });
    }
  );
});

// create new order
app.post('/api/new-order', (req, res) => {
  const { user_id, table_id, order_type } = req.body;

  if (!user_id || !order_type) {
    return res.status(400).json({
      success: false,
      message: 'user_id and order_type are required',
    });
  }

  if (order_type === 'dine-in' && !table_id) {
    return res.status(400).json({
      success: false,
      message: 'table_id is required for dine-in orders',
    });
  }

  const query = `
    INSERT INTO orders (created_by, table_id, type) 
    VALUES (?, ?, ?)
  `;

  db.query(query, [user_id, table_id || null, order_type], (err, result) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({
        success: false,
        message: 'Error creating order',
      });
    }

    const orderId = result.insertId;

    res.status(201).json({
      success: true,
      message: 'Order created',
      data: { order_id: orderId }
    });
  });
});

// add order items
app.post('/api/add-order-item', (req, res) => {
  const { order_id, menu_item_id, quantity, price } = req.body;

  if (!order_id || !menu_item_id || !quantity || !price) {
    return res.status(400).json({
      success: false,
      message: 'order_id, menu_item_id, quantity, and price are required',
    });
  }

  // Step 1: Check if the item already exists in the order
  const checkQuery = `SELECT quantity FROM order_items WHERE order_id = ? AND menu_item_id = ?`;

  db.query(checkQuery, [order_id, menu_item_id], (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({
        success: false,
        message: 'Error checking existing item',
      });
    }

    const revertOrderStatus = (callback) => {
      const revertQuery = `
        UPDATE orders 
        SET status = 'pending', updated_at = CURRENT_TIMESTAMP()
        WHERE id = ? AND status = 'in_progress'
      `;
      db.query(revertQuery, [order_id], (err) => {
        if (err) {
          console.error('Error reverting order status:', err);
        }
        callback(); // Continue response after trying to revert
      });
    };

    if (results.length > 0) {
      // Step 2: If item exists, update quantity
      const newQuantity = results[0].quantity + quantity;

      const updateQuery = `
        UPDATE order_items
        SET quantity = ?, updated_at = CURRENT_TIMESTAMP()
        WHERE order_id = ? AND menu_item_id = ?
      `;

      db.query(updateQuery, [newQuantity, order_id, menu_item_id], (err) => {
        if (err) {
          console.error('Database error (update):', err);
          return res.status(500).json({
            success: false,
            message: 'Error updating item quantity',
          });
        }

        // Step 3: Revert order status if needed
        revertOrderStatus(() => {
          return res.json({
            success: true,
            message: 'Order item quantity updated and order status reverted (if needed)',
          });
        });
      });

    } else {
      // Step 4: Insert new item
      const insertQuery = `
        INSERT INTO order_items (order_id, menu_item_id, quantity, price, status)
        VALUES (?, ?, ?, ?, 'queued')
      `;

      db.query(insertQuery, [order_id, menu_item_id, quantity, price], (err) => {
        if (err) {
          console.error('Database error (insert):', err);
          return res.status(500).json({
            success: false,
            message: 'Error adding new order item',
          });
        }

        // Step 5: Revert order status if needed
        revertOrderStatus(() => {
          return res.status(201).json({
            success: true,
            message: 'Order item added and order status reverted (if needed)',
          });
        });
      });
    }
  });
});

//get order-items / cart
app.get('/api/order-items', (req, res) => {
  const { order_id } = req.query;

  if (!order_id) {
    return res.status(400).json({
      success: false,
      message: 'order_id is required'
    });
  }

  const query = `
  SELECT 
    menu_items.id, 
    menu_items.name, 
    menu_items.price,
    order_items.quantity,
    order_items.status
  FROM order_items 
  JOIN menu_items ON order_items.menu_item_id = menu_items.id
  WHERE order_items.order_id = ?;
`;


  db.query(query, [order_id], (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({
        success: false,
        message: 'Error fetching order items'
      });
    }

    res.json({
      success: true,
      data: results
    });

  });
});

//get order-items (kitchen queue)
app.get('/api/order-items-chef', (req, res) => {

  const query = `
  SELECT 
    menu_items.name,
    order_items.quantity,
    order_items.status,
    order_items.updated_at,
    order_items.order_id,
    order_items.menu_item_id,
    order_items.note,
     CASE 
        WHEN orders.type = 'dine-in' THEN CONCAT('table ',table_id)
        WHEN orders.type = 'take-out' THEN CONCAT('take-out ',orders.id)
        ELSE CONCAT('delivery ',orders.id)
    END AS identifier,
    orders.type,
    orders.table_id
  FROM orders
  JOIN order_items ON orders.id=order_items.order_id
  JOIN menu_items ON order_items.menu_item_id = menu_items.id
  WHERE order_items.status NOT IN ('ready', 'served', 'cancelled')
  AND menu_items.category NOT IN ('BEVERAGES', 'DESSERTS')
  ORDER BY order_items.updated_at ASC
`;


  db.query(query, (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({
        success: false,
        message: 'Error fetching order items'
      });
    }

    res.json({
      success: true,
      data: results
    });

  });
});

//get order-items (drinks queue)
app.get('/api/order-items-drinks', (req, res) => {

  const query = `
  SELECT 
    menu_items.name,
    order_items.quantity,
    order_items.status,
    order_items.updated_at,
    order_items.order_id,
    order_items.menu_item_id,
    order_items.note,
     CASE 
        WHEN orders.type = 'dine-in' THEN CONCAT('table ',table_id)
        WHEN orders.type = 'take-out' THEN CONCAT('take-out ',orders.id)
        ELSE CONCAT('delivery ',orders.id)
    END AS identifier,
    orders.type,
    orders.table_id
  FROM orders
  JOIN order_items ON orders.id=order_items.order_id
  JOIN menu_items ON order_items.menu_item_id = menu_items.id
  WHERE order_items.status NOT IN ('ready', 'served', 'cancelled')
  AND menu_items.category = 'BEVERAGES'
  ORDER BY order_items.updated_at ASC
`;


  db.query(query, (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({
        success: false,
        message: 'Error fetching order items'
      });
    }

    res.json({
      success: true,
      data: results
    });

  });
});

//get order-details / order
app.get('/api/order-details', (req, res) => {
  const { order_id } = req.query;

  if (!order_id) {
    return res.status(400).json({
      success: false,
      message: 'order_id is required'
    });
  }

  const query = `
  SELECT * FROM orders WHERE id = ?;
`;


  db.query(query, [order_id], (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({
        success: false,
        message: 'Error fetching order details'
      });
    }

    res.json({
      success: true,
      data: results
    });
  });
});

//fetch report details 1
app.get('/api/fetch-report-details1', (req, res) => {
  const { date1, date2 } = req.query;

  const query = `
    SELECT
      -- Sales and cost
      SUM(oi.price * oi.quantity) AS revenue,
      SUM(mi.capital_price * oi.quantity) AS cogs,
      SUM(oi.price * oi.quantity) - SUM(mi.capital_price * oi.quantity) AS gross_profit,

      -- Averages
      ROUND(SUM(oi.price * oi.quantity) / NULLIF(DATEDIFF(?, ?) + 1, 0), 2) AS daily_avg,
      ROUND(SUM(oi.price * oi.quantity) / NULLIF(PERIOD_DIFF(DATE_FORMAT(?, '%Y%m'), DATE_FORMAT(?, '%Y%m')) + 1, 0), 2) AS monthly_avg,

      -- Customer (order) averages
      COUNT(DISTINCT o.id) AS total_customers,
      ROUND(COUNT(DISTINCT o.id) / NULLIF(DATEDIFF(?, ?) + 1, 0), 2) AS customers_daily_avg,
      ROUND(COUNT(DISTINCT o.id) / NULLIF(PERIOD_DIFF(DATE_FORMAT(?, '%Y%m'), DATE_FORMAT(?, '%Y%m')) + 1, 0), 2) AS customers_monthly_avg,

      -- Order type counts
      SUM(CASE WHEN o.type = 'dine-in' THEN 1 ELSE 0 END) AS dine_in,
      SUM(CASE WHEN o.type = 'take-out' THEN 1 ELSE 0 END) AS take_out,
      SUM(CASE WHEN o.type = 'delivery' THEN 1 ELSE 0 END) AS delivery
    FROM 
      orders o
    JOIN 
      order_items oi ON o.id = oi.order_id
    JOIN 
      menu_items mi ON oi.menu_item_id = mi.id
    WHERE 
      o.status = 'paid'
      AND DATE(o.created_at) BETWEEN ? AND ?
  `;

  // Pass parameters in the correct order for all placeholders
  const params = [
    date1, date2,      // for daily_avg
    date2, date1,      // for monthly_avg
    date1, date2,      // for customers_daily_avg
    date2, date1,      // for customers_monthly_avg
    date1, date2       // for final WHERE clause
  ];

  db.query(query, params, (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({
        success: false,
        message: 'Error fetching sales data'
      });
    }

    res.json({
      success: true,
      data: results// return single row
    });
  });
});

//fetch report details 2
app.get('/api/fetch-report-details2', (req, res) => {
  const { date1, date2 } = req.query;

  const query = `
    SELECT 
    mi.name AS name,
    SUM(oi.quantity) AS total_quantity
    FROM 
        orders o
    JOIN 
        order_items oi ON o.id = oi.order_id
    JOIN 
        menu_items mi ON oi.menu_item_id = mi.id
    WHERE 
        o.status = 'paid'
        AND DATE(o.created_at) BETWEEN '${date1}' AND '${date2}'
    GROUP BY 
        mi.id, mi.name
    ORDER BY 
        total_quantity DESC
    LIMIT 10
  `;



  db.query(query, (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({
        success: false,
        message: 'Error fetching sales data'
      });
    }

   res.json({
      success: true,
      data: results
    });
  });
});

//fetch report details 3
app.get('/api/fetch-report-details3', (req, res) => {
  const { date1, date2 } = req.query;

  const query = `
    SELECT 
    DATE(p.paid_at) AS date,
    SUM(p.amount) AS total
    FROM 
        payments p
    WHERE 
        DATE(p.paid_at) BETWEEN '${date1}' AND '${date2}'
    GROUP BY 
        DATE(p.paid_at)
    ORDER BY 
    paid_at ASC
  `;



  db.query(query, (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({
        success: false,
        message: 'Error fetching sales data'
      });
    }

   res.json({
      success: true,
      data: results
    });
  });
});

//fetch report details 4
app.get('/api/fetch-report-details4', (req, res) => {
  const { date1, date2 } = req.query;

  const query = `WITH order_summary AS (
  SELECT
    DATE(p.paid_at) AS payment_date,
    o.type,
    oi.menu_item_id,
    mi.name AS menu_item,
    oi.quantity,
    oi.price * oi.quantity AS total
  FROM payments p
  JOIN orders o ON p.order_id = o.id
  JOIN order_items oi ON o.id = oi.order_id
  JOIN menu_items mi ON oi.menu_item_id = mi.id
  WHERE DATE(p.paid_at) BETWEEN ? AND ?
    AND o.status = 'paid'
),
daily_totals AS (
  SELECT
    payment_date,
    SUM(CASE WHEN type = 'dine-in' THEN total ELSE 0 END) AS dine_in_sales,
    SUM(CASE WHEN type = 'take-out' THEN total ELSE 0 END) AS take_out_sales,
    SUM(CASE WHEN type = 'delivery' THEN total ELSE 0 END) AS delivery_sales,
    SUM(total) AS total_sales
  FROM order_summary
  GROUP BY payment_date
),
best_sellers AS (
  SELECT
    payment_date,
    menu_item,
    SUM(quantity) AS total_quantity,
    ROW_NUMBER() OVER (PARTITION BY payment_date ORDER BY SUM(quantity) DESC) AS rn
  FROM order_summary
  GROUP BY payment_date, menu_item
)
SELECT
  dt.payment_date,
  dt.dine_in_sales,
  dt.take_out_sales,
  dt.delivery_sales,
  dt.total_sales,
  bs.menu_item AS best_seller,
  bs.total_quantity AS best_seller_qty
FROM daily_totals dt
LEFT JOIN best_sellers bs 
  ON dt.payment_date = bs.payment_date 
  AND bs.rn = 1
ORDER BY dt.payment_date ASC;

  `;



  db.query(query, [date1, date2], (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({
        success: false,
        message: 'Error fetching sales data'
      });
    }

   res.json({
      success: true,
      data: results
    });
  });
});

// update order details
app.post('/api/update-order-item', (req, res) => {
  const { order_id, menu_item_id, quantity } = req.body;

  if (!order_id || !menu_item_id || quantity == null) {
    return res.status(400).json({
      success: false,
      message: 'order_id, menu_item_id, and quantity are required',
    });
  }

  const query = `
    UPDATE order_items
    SET quantity = ?
    WHERE order_id = ? AND menu_item_id = ?
  `;

  db.query(query, [quantity, order_id, menu_item_id], (err, result) => {
    if (err) {
      console.error('DB Error:', err);
      return res.status(500).json({
        success: false,
        message: 'Failed to update quantity',
      });
    }

    res.json({ success: true, message: 'Quantity updated' });
  });
});

// disable menu item
app.post('/api/disable-menu', (req, res) => {
  const {menu_item_id} = req.body;

  if (!menu_item_id) {
    return res.status(400).json({
      success: false,
      message: 'menu_item_id is required',
    });
  }

  const query = `
    UPDATE menu_items
    SET is_available = NOT is_available
    WHERE id = ?
  `;

  db.query(query, [menu_item_id], (err, result) => {
    if (err) {
      console.error('DB Error:', err);
      return res.status(500).json({
        success: false,
        message: 'Failed to update menu item',
      });
    }

    res.json({ success: true, message: 'Menu item updated' });
  });
});

// disable user
app.post('/api/disable-user', (req, res) => {
  const {user_id} = req.body;

  if (!user_id) {
    return res.status(400).json({
      success: false,
      message: 'user_id is required',
    });
  }

  const query = `
    UPDATE users
    SET is_active = NOT is_active
    WHERE id = ?
  `;

  db.query(query, [user_id], (err, result) => {
    if (err) {
      console.error('DB Error:', err);
      return res.status(500).json({
        success: false,
        message: 'Failed to update user',
      });
    }

    res.json({ success: true, message: 'User updated' });
  });
});

// save payment
app.post('/api/save-payment', (req, res) => {
  const { order_id, total_amount, discount_total, discounted_amount, user_id, discount_id} = req.body;

  if (!order_id || total_amount == null || discount_total == null || discounted_amount == null || !user_id) {
    return res.status(400).json({
      success: false,
      message: 'All fields are required',
    });
  }

  const save_payment = `
    INSERT INTO payments (order_id, amount, method, received_by)
    VALUES (?, ?, ?, ?)
  `;

  const update_order = `
    UPDATE orders 
    SET status = 'paid', total_amount = ?, discount_total = ?, completed_at = CURRENT_TIMESTAMP()
    WHERE id = ?
  `;

  // Step 1: Save payment
  db.query(save_payment, [order_id, discounted_amount, 'cash', user_id], (err) => {
    if (err) {
      console.error('DB Error (save payment):', err);
      return res.status(500).json({
        success: false,
        message: 'Failed to save payment',
      });
    }

    // Step 2: Update order
    db.query(update_order, [discounted_amount, discount_total, order_id], (err) => {
      if (err) {
        console.error('DB Error (update order):', err);
        return res.status(500).json({
          success: false,
          message: 'Failed to update order',
        });
      }

      // Step 3: Save discount if applicable
      if (Number.isInteger(discount_id)) {
        const save_discount = `
          INSERT INTO order_discounts (order_id, discount_id, amount)
          VALUES (?, ?, ?)
        `;
        db.query(save_discount, [order_id, discount_id, discount_total], (err) => {
          if (err) {
            console.error('DB Error (save discount):', err);
            return res.status(500).json({
              success: false,
              message: 'Failed to save discount',
            });
          }

          // Only send success after all operations complete
          return res.json({
            success: true,
            message: 'Payment saved, order updated, and discount recorded',
          });
        });
      } else {
        // No discount to save, return success right after updating order
        return res.json({
          success: true,
          message: 'Payment saved and order marked as paid (no discount)',
        });
      }
    });
  });
});

// update order table
app.post('/api/update-order-table', (req, res) => {
  const { table_id, order_id } = req.body;

  if (!order_id || !table_id == null) {
    return res.status(400).json({
      success: false,
      message: 'order_id, table_id are required',
    });
  }

  const query = `UPDATE orders SET table_id = ? WHERE id = ?`;

  db.query(query, [table_id, order_id], (err, result) => {
    if (err) {
      console.error('DB Error:', err);
      return res.status(500).json({
        success: false,
        message: 'Failed to update table',
      });
    }

    res.json({ success: true, message: 'table updated' });
  });
});

// update order status (kitchen: done)
app.post('/api/update-order-status-done', (req, res) => {
  const { order_id, menu_item_id } = req.body;

  if (!order_id || !menu_item_id == null) {
    return res.status(400).json({
      success: false,
      message: 'order_id, menu_item_id are required',
    });
  }

  const query = `UPDATE order_items SET status = 'ready'
  WHERE order_items.order_id = ? AND order_items.menu_item_id = ?`;

  db.query(query, [order_id, menu_item_id], (err, result) => {
    if (err) {
      console.error('DB Error:', err);
      return res.status(500).json({
        success: false,
        message: 'Failed to status',
      });
    }

    res.json({ success: true, message: 'order item updated' });
  });
});

// update order status (waiter: serve)
app.post('/api/update-order-status-serve', (req, res) => {
  const { order_id, menu_item_id } = req.body;

  if (!order_id || menu_item_id == null) {
    return res.status(400).json({
      success: false,
      message: 'order_id and menu_item_id are required',
    });
  }

  // Step 1: Update the order item status to 'served'
  const updateItemQuery = `
    UPDATE order_items 
    SET status = 'served', updated_at = CURRENT_TIMESTAMP()
    WHERE order_id = ? AND menu_item_id = ?
  `;

  db.query(updateItemQuery, [order_id, menu_item_id], (err, result) => {
    if (err) {
      console.error('DB Error:', err);
      return res.status(500).json({
        success: false,
        message: 'Failed to update item status',
      });
    }

    // Step 2: Check if all items for this order are now served
    const checkRemainingQuery = `
      SELECT COUNT(*) AS remaining 
      FROM order_items 
      WHERE order_id = ? AND status != 'served'
    `;

    db.query(checkRemainingQuery, [order_id], (err, rows) => {
      if (err) {
        console.error('DB Error:', err);
        return res.status(500).json({
          success: false,
          message: 'Failed to check item statuses',
        });
      }

      const remaining = rows[0].remaining;

      // Step 3: If all are served, update the order status
      if (remaining === 0) {
        const updateOrderQuery = `
          UPDATE orders 
          SET status = 'in_progress', updated_at = CURRENT_TIMESTAMP()
          WHERE id = ?
        `;
        db.query(updateOrderQuery, [order_id], (err) => {
          if (err) {
            console.error('DB Error:', err);
            return res.status(500).json({
              success: false,
              message: 'Failed to update order status',
            });
          }

          return res.json({
            success: true,
            message: 'Order item and order status updated to in_progress',
          });
        });
      } else {
        // Only item status updated, not the order
        return res.json({
          success: true,
          message: 'Order item updated; waiting for remaining items to be served',
        });
      }
    });
  });
});

// remove cart if 0 or delete item 
app.post('/api/delete-order-item', (req, res) => {
  const { order_id, menu_item_id } = req.body;

  if (!order_id || !menu_item_id) {
    return res.status(400).json({
      success: false,
      message: 'order_id and menu_item_id are required',
    });
  }

  const deleteQuery = `
    DELETE FROM order_items 
    WHERE order_id = ? AND menu_item_id = ?
  `;

  db.query(deleteQuery, [order_id, menu_item_id], (err, result) => {
    if (err) {
      console.error('DB error (delete item):', err);
      return res.status(500).json({
        success: false,
        message: 'Failed to delete order item',
      });
    }

    // Step 2: Check if any order_items remain for this order
    const checkRemainingQuery = `
      SELECT COUNT(*) AS remaining FROM order_items WHERE order_id = ?
    `;

    db.query(checkRemainingQuery, [order_id], (err, results) => {
      if (err) {
        console.error('DB error (check remaining):', err);
        return res.status(500).json({
          success: false,
          message: 'Failed to check remaining items',
        });
      }

      const remaining = results[0].remaining;

      if (remaining === 0) {
        // Step 3: Delete the order if no more items
        const deleteOrderQuery = `DELETE FROM orders WHERE id = ?`;

        db.query(deleteOrderQuery, [order_id], (err) => {
          if (err) {
            console.error('DB error (delete order):', err);
            return res.status(500).json({
              success: false,
              message: 'Failed to delete empty order',
            });
          }

          return res.json({
            success: true,
            message: 'Order item and empty order deleted successfully',
          });
        });
      } else {
        // Order still has items, no need to delete
        return res.json({
          success: true,
          message: 'Order item removed successfully',
        });
      }
    });
  });
});

// send to kitchen
app.post('/api/send-to-kitchen', (req, res) => {
  const { order_id, items } = req.body;

  if (!order_id || !Array.isArray(items)) {
    return res.status(400).json({ success: false, message: 'Invalid input' });
  }

  const updatePromises = items.map(({ menu_item_id, note }) => {
    const query = note
      ? `UPDATE order_items SET status = 'preparing', note = ? WHERE order_id = ? AND menu_item_id = ? AND status = 'queued'`
      : `UPDATE order_items SET status = 'preparing' WHERE order_id = ? AND menu_item_id = ? AND status = 'queued'`;

    const params = note ? [note, order_id, menu_item_id] : [order_id, menu_item_id];

    return new Promise((resolve, reject) => {
      db.query(query, params, (err, result) => {
        if (err) reject(err);
        else resolve();
      });
    });
  });

  Promise.all(updatePromises)
    .then(() => res.json({ success: true, message: 'Sent to kitchen' }))
    .catch(err => {
      console.error(err);
      res.status(500).json({ success: false, message: 'Database error' });
    });
});



const PORT = process.env.PORT;

app.listen(PORT, '0.0.0.0', () => {
  console.log(`Server running at http://0.0.0.0:${PORT}`);
});