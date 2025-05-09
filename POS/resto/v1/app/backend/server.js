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
  port: process.env.DB_PORT || 3001 // if your server uses a custom port
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
  
// Example route
app.get('/api/login', (req, res) => {
  db.query('SELECT * FROM users', (err, results) => {
    if (err) return res.status(500).json({ error: err });
    res.json(results);
  });
});

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
  SELECT 
    id AS order_id,
    type AS order_type,
    CASE 
        WHEN type = 'dine-in' THEN CONCAT('table#',table_id)
        ELSE id
    END AS identifier
FROM orders
WHERE orders.status='pending'
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

// Create new menu item
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

  db.query(checkQuery, [order_id, menu_item_id, price], (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({
        success: false,
        message: 'Error checking existing item',
      });
    }

    if (results[0]) {
      // Step 2: If exists, update quantity
      const newQuantity = results[0].quantity + quantity;

      const updateQuery = `
        UPDATE order_items
        SET quantity = ?
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

        return res.json({
          success: true,
          message: 'Order item quantity updated',
        });
      });

    } else {
      // Step 3: If not exists, insert new row
      const insertQuery = `
        INSERT INTO order_items (order_id, menu_item_id, quantity, price)
        VALUES (?, ?, ?, ?)
      `;

      db.query(insertQuery, [order_id, menu_item_id, quantity, price], (err) => {
        if (err) {
          console.error('Database error (insert):', err);
          return res.status(500).json({
            success: false,
            message: 'Error adding new order item',
          });
        }

        return res.status(201).json({
          success: true,
          message: 'Order item added',
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

// remove cart if 0 or delete item 
app.post('/api/delete-order-item', (req, res) => {
  const { order_id, menu_item_id } = req.body;

  if (!order_id || !menu_item_id) {
    return res.status(400).json({
      success: false,
      message: 'order_id and menu_item_id are required',
    });
  }

  const query = `
    DELETE FROM order_items 
    WHERE order_id = ? AND menu_item_id = ?
  `;

  db.query(query, [order_id, menu_item_id], (err, result) => {
    if (err) {
      console.error('DB error:', err);
      return res.status(500).json({
        success: false,
        message: 'Failed to delete order item',
      });
    }

    res.json({
      success: true,
      message: 'Order item removed successfully',
    });
  });
});

// send to kitchen
app.post('/api/send-to-kitchen', (req, res) => {
  const { order_id } = req.body;

  if (!order_id == null) {
    return res.status(400).json({
      success: false,
      message: 'order_id required',
    });
  }

  const query = `UPDATE order_items SET status = 'preparing' WHERE order_id = ?`;

  db.query(query, [order_id], (err, result) => {
    if (err) {
      console.error('DB Error:', err);
      return res.status(500).json({
        success: false,
        message: 'Failed to send to kitchen',
      });
    }

    res.json({ success: true, message: 'sent to kitchen' });
  });
});

const PORT = process.env.PORT || 3001;
app.listen(PORT, () => console.log(`Backend server running on port ${PORT}`));
