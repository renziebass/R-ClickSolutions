import { useState, useContext, useEffect } from 'react';
import { useRequireAuth } from '../contexts/AuthContext';
import { AuthContext } from '../contexts/AuthContext';
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/react';
import {
    ChevronDownIcon,
    Cog6ToothIcon,
    QuestionMarkCircleIcon,
    ArrowLeftCircleIcon,
    ViewColumnsIcon,
    XMarkIcon,
  } from "@heroicons/react/24/outline";

function WaiterUi() {
  const user = useRequireAuth();
  const [cart, setCart] = useState([]);
  const [activeCategory, setActiveCategory] = useState('all');
  const [orderId, setOrderId] = useState('');
  const [orderStatus, setOrderStatus] = useState('pending');
  const [vacantTables, setVacantTables] = useState([]);
  const [orders, setOrders] = useState([]);
  const [orderDetails, setOrderDetails] = useState({
    order_id: '',
    user_id: user.id,
    table_id: '',
    menu_item_id: '',
    quantity: 1,
    order_type: ''
   
  });
  const [orderType, setOrderType] = useState('dine-in');
  const [tableNumber, setTableNumber] = useState('');
  const { logout } = useContext(AuthContext);
  const [menuItems, setMenuItems] = useState([]);
  const [searchQuery, setSearchQuery] = useState('');
  const [heldOrders, setHeldOrders] = useState([]);
  const [orderNotes, setOrderNotes] = useState({});
  const [discount, setDiscount] = useState(0);
  const [isMobileCartOpen, setIsMobileCartOpen] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');
  const [category, setCategory] = useState('all');
  const [availability, setAvailability] = useState('all');
  const [search, setSearch] = useState('');
  const [CategoryItems, setCategoryItems] = useState([]);

const handleSignOut = (e) => {
    e.preventDefault();
    logout();
};

//fetch categories
const fetchCategory = async () => {
  setIsLoading(true);
  try {
    const response = await fetch('http://localhost:3306/api/categories'); // Update the API endpoint
    const data = await response.json();
    if (data.success) {
      setCategoryItems(data.data);
      setErrorMessage('');
    } else {
      setErrorMessage(data.message);
    }
  } catch (error) {
    setErrorMessage('Error fetching categories');
    console.error('Error:', error);
  } finally {
    setIsLoading(false);
  }
};
  
const fetchAvailableTables = async () => {

    try {
      const response = await fetch('http://localhost:3306/api/fetch-tables'); // Update the API endpoint
      const data = await response.json();
      if (data.success) {
        setVacantTables(data.data);
        console.log('Vacant tables:', data.data); // Debug line
        setErrorMessage('');
      } else {
        setErrorMessage(data.message);
      }
    } catch (error) {
      setErrorMessage('Error fetching tables');
      console.error('Error:', error);
    } finally {
   
    }
};

const fetchPendingOrders = async () => {
  setIsLoading(true);
  try {
    const response = await fetch('http://localhost:3306/api/fetch-pending-orders'); // Update the API endpoint
    const data = await response.json();
    if (data.success) {
      setOrders(data.data);
      setErrorMessage('');
    } else {
      setErrorMessage(data.message);
    }
  } catch (error) {
    setErrorMessage('Error fetching orders');
    console.error('Error:', error);
  } finally {
    setIsLoading(false);
  }
};

const fetchOrderlist = async (orderId) => {
 
  try {
    const response = await fetch(`http://localhost:3306/api/order-items?order_id=${orderId}`);
    const data = await response.json();
    console.log('Fetched cart data:', data); // 🔍 LOG IT HERE

    if (data.success) {
      setCart(data.data);
      setErrorMessage('');
    } else {
      setErrorMessage(data.message);
    }
  } catch (error) {
    setErrorMessage('Error fetching orders');
    console.error('Error:', error);
  } finally {

  }
};

const fetchOrderDetails = async (orderId) => {
 
  try {
    const response = await fetch(`http://localhost:3306/api/order-details?order_id=${orderId}`);
    const data = await response.json();
    setOrderDetails({
      order_id: '',
      table_id: '',
      order_type: '',
      // add more fields if needed
    });

    if (data.success) {
      const order = data.data[0];

        setOrderDetails({
        order_id: order.id ?? '',
        table_id: order.table_id ?? '',
        order_type: order.type ?? '', // Make sure this key matches your DB
      });
      fetchOrderlist(order.id ?? '');
      setErrorMessage('');
    } else {
      setErrorMessage(data.message);
    }
  } catch (error) {
    setErrorMessage('Error fetching orders');
    console.error('Error:', error);
  } finally {

  }
};

const fetchMenuItems = async () => {

  try {
    const response = await fetch('http://localhost:3306/api/menu-items');
    const data = await response.json();
    if (data.success) {
      setMenuItems(data.data);
      setErrorMessage('');
    } else {
      setErrorMessage(data.message);
    }
  } catch (error) {
    setErrorMessage('Error fetching menu items');
    console.error('Error:', error);
  } finally {
    setIsLoading(false);
  }
};

const filteredItems = menuItems.filter(item => {
  const matchSearch = item.name.toLowerCase().includes(searchQuery.toLowerCase());
  const matchCategory = activeCategory === 'all' || item.category === activeCategory;
  const matchStatus = availability === 'all' || 
    (availability === '1' && item.is_available === 1) || 
    (availability === '0' && item.is_available === 0);
  return matchSearch && matchCategory && matchStatus;
});



  
const createOrder = async () => {
  const { user_id, order_type, table_id } = orderDetails;

  if (order_type === 'dine-in' && !table_id) {
    alert('Table number required for dine-in');
    return false;
  }

  try {
    const response = await fetch('http://localhost:3306/api/new-order', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id, order_type, table_id }),
    });

    const data = await response.json();
    if (data.success) {
      setOrderDetails((prev) => ({
        ...prev,
        order_id: data.data.order_id,
      }));
      return data.data.order_id;
    } else {
      setErrorMessage(data.message);
      return false;
    }
  } catch (err) {
    setErrorMessage('Failed to create order');
    return false;
  }
};

const addToCart = async (item) => {
    let orderId = orderDetails.order_id;
  
    // Create order only if not created yet
    if (!orderId) {
      const createdOrderId = await createOrder();
      if (!createdOrderId) return; // failed to create
      orderId = createdOrderId;
    }
  
    try {
      const response = await fetch('http://localhost:3306/api/add-order-item', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          order_id: orderId,
          menu_item_id: item.id,
          quantity: 1,
          price: item.price.toFixed(2),
        }),
      });
  
      const data = await response.json();
  
      if (data.success) {
        setCart((prev) => {
          const existingItem = prev.find((cartItem) => cartItem.id === item.id);
          
          if (existingItem) {
            // Update quantity of the existing item
            return prev.map((cartItem) =>
              cartItem.id === item.id
                ? { ...cartItem, quantity: cartItem.quantity + 1 }
                : cartItem
            );
          } else {
            // Add new item
            return [...prev, { ...item, quantity: 1 }];
          }
        });
        
      } else {
        setErrorMessage(data.message);
      }
    } catch (error) {
      console.error(error);
      setErrorMessage('Failed to add item to order');
    }
};
  
const updateQuantity = async (menuItemId, newQty) => {
    if (newQty < 1) {
      removeFromCart(menuItemId); // Optional: auto-remove if quantity goes below 1
      return;
    }
  
    try {
      const response = await fetch('http://localhost:3306/api/update-order-item', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          order_id: orderDetails.order_id,
          menu_item_id: menuItemId,
          quantity: newQty,
        }),
      });
  
      const data = await response.json();
      if (data.success) {
        // Update local cart state
        setCart((prevCart) =>
          prevCart.map((item) =>
            item.id === menuItemId ? { ...item, quantity: newQty } : item
          )
        );
      } else {
        setErrorMessage(data.message);
      }
    } catch (err) {
      console.error('Error updating quantity:', err);
      setErrorMessage('Failed to update quantity');
    }
};

const updateOrderTable = async () => {

  try {
    const response = await fetch('http://localhost:3306/api/update-order-table', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        order_id: orderDetails.table_id,
        table_id: orderDetails.order_id,
      }),
    });
    

    const data = await response.json();
    if (data.success) {
      setOrderDetails({
        order_id: "",
        order_type: "",
        table_id: "",
        // add other fields you use in orderDetails
      });
      fetchPendingOrders();
      fetchOrderlist();
    } else {
      setErrorMessage(data.message);
    }
  } catch (err) {
    console.error('Error updating table:', err);
    setErrorMessage('Failed to update table');
  }
};

const removeFromCart = async (menuItemId) => {
    try {
      const response = await fetch('http://localhost:3306/api/delete-order-item', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          order_id: orderDetails.order_id,
          menu_item_id: menuItemId,
        }),
      });
  
      const data = await response.json();
      if (data.success) {
        setCart((prevCart) =>
          prevCart.filter((item) => item.id !== menuItemId)
        );
      } else {
        setErrorMessage(data.message);
      }
    } catch (error) {
      console.error('Error deleting item:', error);
      setErrorMessage('Failed to remove item');
    }
};
  
const calculateTotal = () => {
    return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
};

const addNote = (itemId, note) => {
    setOrderNotes({
      ...orderNotes,
      [itemId]: note
    });
};

const holdOrder = () => {
    if (cart.length > 0) {
      setHeldOrders([...heldOrders, { 
        items: cart, 
        notes: orderNotes, 
        tableNumber,
        orderType,
        timestamp: new Date()
      }]);
      setCart([]);
      setOrderNotes({});
    }
};

const sendToKitchen = () => {
    // Implement kitchen queue logic here
    alert('Order sent to kitchen!');
};

useEffect(() => {
  fetchMenuItems();
  fetchCategory();
  fetchPendingOrders();
  if (orderDetails.order_type === '') {
    fetchAvailableTables();
  }
  // Optionally, clear table_id if not dine-in
  else {
    setOrderDetails({table_id: '' });
  }
}, []); 

  return (
    <div className="h-screen flex flex-col">
      {/* Top Navigation */}
      <div className="bg-[#2B4F4B] flex items-center">
        {/* Mobile Cart Toggle */}
        <button 
          className="lg:hidden text-white flex space-x-2 relative"
          onClick={() => setIsMobileCartOpen(!isMobileCartOpen)}
        >
        <ViewColumnsIcon className="h-6 w-6 ml-3" />
          {cart.length > 0 && (
            <span className="top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 text-xs flex items-center justify-center">
            {cart.length}
          </span>
          )}
        </button>
        <Menu as="div" className="w-full flex justify-end">
          <MenuButton as="div" className="gap-2 p-2 hover:bg-gray-200 ">
            <span className="flex items-center gap-2 text-white">
              <span className="min-w-0 text-end">
                <span className="block truncate text-sm/5 font-medium">
                  {user?.id}
                </span>
                <span className="block truncate text-xs/5 font-normal">
                  {user?.role}
                </span>
              </span>
              <img 
                className="h-10 w-10 rounded-full object-cover"
                src="https://randomuser.me/api/portraits/women/68.jpg"
                alt="User Avatar"
              />
              <ChevronDownIcon className="h-5 w-5" />
            </span>
          </MenuButton>
          <MenuItems
            transition
            className="absolute right-0 z-10 mt-2 w-45 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black/5"
          >
            <div className="py-1">
              <MenuItem>
                <a className="flex items-center gap-2 px-2 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  <Cog6ToothIcon className="size-5 text-gray-600" />
                  Account settings
                </a>
              </MenuItem>
              <MenuItem>
                <a className="flex items-center gap-2 px-2 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  <QuestionMarkCircleIcon className="size-5 text-gray-600" />
                  Support
                </a>
              </MenuItem>
              <MenuItem>
                <button
                  onClick={handleSignOut}
                  className="flex w-full items-center gap-2 px-2 py-2 text-sm text-gray-700 hover:bg-gray-100"
                >
                  <ArrowLeftCircleIcon className="size-5 text-gray-600" />
                  Sign out
                </button>
              </MenuItem>
            </div>
          </MenuItems>
        </Menu>

        {/* User Menu - Moved to top */}
       
      </div>

      <div className="flex-1 flex flex-col lg:flex-row">
        {/* Categories - Horizontal scroll on mobile */}
        <div className="lg:w-1/6 bg-gray-800 p-4 overflow-x-auto lg:overflow-y-auto flex flex-col lg:flex-col gap-2 ">
          <div className="flex lg:flex-col gap-2 min-w-max">
            <button className={`p-3 lg:p-2 rounded whitespace-nowrap ${
                  activeCategory === category.name
                    ? 'bg-blue-600 text-white' 
                    : 'bg-gray-700 text-white hover:bg-gray-600'
                }`}
                onClick={() => setActiveCategory('all')}>
                  ALL
            </button>
            {CategoryItems.map(category => (
              <button
                key={category.name}
                className={`p-3 lg:p-2 rounded whitespace-nowrap ${
                  activeCategory === category.name
                    ? 'bg-blue-600 text-white' 
                    : 'bg-gray-700 text-white hover:bg-gray-600'
                }`}
                onClick={() => setActiveCategory(category.name)}
              >
                {category.name}
              </button>
            ))}
          </div>
        </div>

        {/* Menu Items Grid - Responsive columns */}
        <div className="flex-1 p-4">
          {/* Mobile Search */}
          <input
            type="text"
            placeholder="Search menu..."
            className="w-full p-2 rounded mb-4 border-1"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
          />

          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            {filteredItems.map(item => (
                <button
                  key={item.id}
                  className="p-1 bg-white rounded-lg border hover:shadow-md flex flex-col"
                  onClick={() => addToCart(item)}
                >
                  <div className="h-24 md:h-32 bg-gray-200 rounded-md">
                    {/* Add product image here */}
                  </div>
                  <h3 className="font-bold text-sm md:text-sm">{item.name}</h3>
                  <p className="text-gray-600">₱{item.price.toFixed(2)}</p>
                </button>
              ))}
          </div>
        </div>

        {/* Cart - Slide in from right on mobile */}
        <div className={`
          fixed lg:relative right-0 top-0 h-full w-full lg:w-1/3 
          bg-white shadow-lg transform transition-transform duration-300 ease-in-out
          ${isMobileCartOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'}
          z-50 lg:z-0
        `}>
          {/* Mobile Cart Header */}
          <div className="lg:hidden flex items-center justify-between p-2 bg-gray-800 text-white">
            <h2 className="text-sm">Current Order</h2>
            <button onClick={() => setIsMobileCartOpen(false)}>
              <XMarkIcon className="h-6 w-6" />
            </button>
          </div>

          {/* Cart Content */}
          <div className="flex flex-col h-full overflow-y-auto">
            {/* Order Type & Table */}
            <div className="p-1 border-b w-full">
  
            <select
                className="p-2 border rounded m-2"
                value={orderDetails.order_id}
                onChange={(e) => {
                  const orderId = e.target.value;
                  fetchOrderDetails(orderId);
                }}
              >
                <option value="" selected disabled hidden>Select Orders</option>

                {orders.map((orderslist) => (
                  <option
                    key={orderslist.order_id}
                    value={orderslist.order_id} // Important: set value to order_id
                  >
                    {orderslist.order_type}/{orderslist.identifier}
                  </option>
                ))}
              </select>

              <select 
                className="p-2 border rounded m-2"
                value={orderDetails.order_type}
                onChange={(e) => setOrderDetails({...orderDetails, order_type: e.target.value})}
                hidden={orderDetails.order_id !== null}
              >
                <option value="" selected disabled hidden>Order type</option>
                <option value="dine-in">Dine-in</option>
                <option value="take-out">Take-out</option>
                <option value="delivery">Delivery</option>
              </select>

              {orderDetails.order_type === 'dine-in' && (
                <select 
                  className="p-2 border rounded m-2"
                  value={orderDetails.table_id}
                  onChange={(e) => setOrderDetails({...orderDetails, table_id: Number(e.target.value)})}
                >
                  <option value="" selected disabled hidden>Table #</option>
                  {vacantTables.map((item, index) => (
                    <option key={index} value={item.id}>
                      {item.name}
                    </option>
                  ))}
                </select>
              )}
              {/* Confirm Button */}
              <button
                className="p-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                hidden={!orderDetails.order_id || !orderDetails.table_id}
                onClick={() => {
                  // Call your update API here
                  updateOrderTable();
                }}
              >
                Update Table
              </button>
            </div>

            {/* Cart Items */}
            <div className="h-screen overflow-y-auto p-2">
              {cart.map(item => (
                <div key={item.id} className="mb-1 rounded-lg p-3">
                  <div className="flex justify-between items-start">
                    <div>
                      <h4 className="font-bold">{item.name}</h4>
                      <p className="text-gray-600">₱{item.price.toFixed(2)}</p>
                    </div>
                    <div className="flex items-center gap-2">
                      <button 
                        className="px-2 py-1 bg-gray-200 rounded"
                        onClick={() => updateQuantity(item.id, item.quantity - 1)}
                      >-</button>
                      <span>{item.quantity}</span>
                      <button 
                        className="px-2 py-1 bg-gray-200 rounded"
                        onClick={() => updateQuantity(item.id, item.quantity + 1)}
                      >+</button>
                    </div>
                  </div>
                  
                  <div className="flex gap-2">
                    <input
                      type="text"
                      placeholder="Add note..."
                      className="flex-1 p-2 text-xs border rounded"
                      value={orderNotes[item.id] || ''}
                      onChange={(e) => addNote(item.id, e.target.value)}
                    />
                    <button 
                      className="text-red-500"
                      onClick={() => removeFromCart(item.id)}
                    >Remove</button>
                  </div>
                </div>
              ))}
            </div>

            {/* Order Actions */}
            <div className="p-1 border-t bg-gray-50 mb-10 sticky bottom-0 left-0"> 
              <div className="flex justify-between mb-1">
                <span>Subtotal:</span>
                <span>₱{calculateTotal().toFixed(2)}</span>
              </div>

              <div className="flex gap-2">
                <button 
                  className="flex-1 bg-yellow-500 text-white py-2 rounded hover:bg-yellow-600"
                  onClick={holdOrder}
                >
                  Hold Order
                </button>
                <button 
                  className="flex-1 bg-blue-500 text-white py-2 rounded hover:bg-blue-600"
                  onClick={sendToKitchen}
                >
                  Send to Kitchen
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default WaiterUi;