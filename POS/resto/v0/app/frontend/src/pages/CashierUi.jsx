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
  ArrowPathIcon,
  QueueListIcon
} from "@heroicons/react/24/outline";
import PaymentModal from '../components/PaymentModal';
import.meta.env.VITE_API_BASE_URL;
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';

dayjs.extend(relativeTime);
dayjs.extend(utc);
dayjs.extend(timezone)
dayjs.tz.setDefault("Asia/Taipei")


function CashierUi() {
  const [isPaymentModalOpen, setIsPaymentModalOpen] = useState(false);
  const [currentTime, setCurrentTime] = useState(new Date());
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
    order_type: '',
    notes: '',
    order_status: '',

  });
  const [orderType, setOrderType] = useState('dine-in');
  const [tableNumber, setTableNumber] = useState('');
  const { logout } = useContext(AuthContext);
  const [menuItems, setMenuItems] = useState([]);
  const [searchQuery, setSearchQuery] = useState('');
  const [orderNotes, setOrderNotes] = useState({});
  const [discounts, setDiscounts] = useState([]);
  const [discount, setDiscount] = useState(null);

  const [isMobileCartOpen, setIsMobileCartOpen] = useState(false);

  const [errorMessage, setErrorMessage] = useState('');
  const [category, setCategory] = useState('all');
  const [availability, setAvailability] = useState('all');
  const [search, setSearch] = useState('');
  const [CategoryItems, setCategoryItems] = useState([]);
  const [isFormOpen, setIsFormOpen] = useState(true);
  const [queue, setQueue] = useState([]);
  const [lastRefreshTime, setLastRefreshTime] = useState('');
  const [isLoadingOrderList, setIsLoadingOrderList] = useState(false);
  const [isLoadingCategoryList, setIsLoadingCategoryList] = useState(false);
  const [isLoadingSend, setIsLoadingSend] = useState(false);
  const [isLoadingPayment, setIsLoadingPayment] = useState(false);
  const [isLoadingMenuItems, setIsLoadingMenuItems] = useState(false);
  const [isLoadingDrinkQueue, setIsLoadingDrinkQueue] = useState(false);
  const [loadingItemId, setLoadingItemId] = useState(null); //Add to Cart loading
  const [loadingDoneItemId, setLoadingDoneItemId] = useState(null); //Done Queue loading
  const [loadingServerId, setLoadingServerId] = useState(null);
  

  const handleSignOut = (e) => {
    e.preventDefault();
    logout();
  };

//fetch categories
  const fetchCategory = async () => {
    setIsLoadingCategoryList(true);
    try {
      const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/categories`); // Update the API endpoint
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
      setIsLoadingCategoryList(false);
    }
  };

  const fetchQueuelist = async () => {
    setIsLoadingDrinkQueue(true);
    try {
      const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/order-items-drinks`);
      const data = await response.json();

      if (data.success) {
        setQueue(data.data);
        setErrorMessage('');
        setLastRefreshTime(new Date().toLocaleTimeString());
      } else {
        setErrorMessage(data.message);
      }
    } catch (error) {
      setErrorMessage('Error fetching orders');
      console.error('Error:', error);
    } finally {
      setIsLoadingDrinkQueue(false);
    }
  };

  /*const fetchDiscounts = async () => {
  
    try {
      const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/discounts`); // Update the API endpoint
      const data = await response.json();
      if (data.success) {
        setDiscounts(data.data);
        setErrorMessage('');
      } else {
        setErrorMessage(data.message);
      }
    } catch (error) {
      setErrorMessage('Error fetching discounts');
      console.error('Error:', error);
    } finally {

    }
  };
  */

  const fetchAvailableTables = async () => {

    try {
      const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/fetch-tables`); // Update the API endpoint
      const data = await response.json();
      if (data.success) {
        setVacantTables(data.data);
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

    try {
      const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/fetch-pending-orders`); // Update the API endpoint
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

    }
  };

  const fetchOrderlist = async (orderId) => {
    setIsLoadingOrderList(true)
    try {
      const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/order-items?order_id=${orderId}`);
      const data = await response.json();

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
      setIsLoadingOrderList(false)
    }
  };

  const fetchOrderDetails = async (orderId) => {

    try {
      const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/order-details?order_id=${orderId}`);
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
          order_type: order.type ?? '',
          order_status: order.status ?? '',
        });
        
        setTableNumber(order.table_id ?? '');
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
    setIsLoadingMenuItems(true);
    try {
      const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/menu-items`);
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
      setIsLoadingMenuItems(false);
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
    if (!order_type) {
      alert('Order type required!')
      return false;
    }

    try {
      const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/new-order`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id, order_type, table_id }),
      });

      const data = await response.json();
      if (data.success) {
        fetchPendingOrders();
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
    } finally { 
   
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
      const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/add-order-item`, {
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
        fetchOrderDetails(orderId);
      } else {
        setErrorMessage(data.message);
      }
    } catch (error) {
      console.error(error);
      setErrorMessage('Failed to add item to order');
    } finally {
  
    }
  };

  const updateQuantity = async (menuItemId, newQty) => {
 
    if (newQty < 1) {
      removeFromCart(menuItemId); // Optional: auto-remove if quantity goes below 1
      return;
    }

    try {
      const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/update-order-item`, {
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
            item.order_id === menuItemId ? { ...item, quantity: newQty } : item
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
      const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/update-order-table`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          order_id: orderDetails.order_id,
          table_id: orderDetails.table_id,
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

  const removeFromCart = async (orderId,menuItemId) => {
    try {
      const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/delete-order-item`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          order_id: orderId,
          menu_item_id: menuItemId,
        }),
      });

      const data = await response.json();
      if (data.success) {

        setCart((prevCart) => {
          const updatedCart = prevCart.filter((item) => item.menu_id !== menuItemId);

          if (updatedCart.length === 0) {
            fetchOrderDetails(orderDetails.order_id);
          }

          return updatedCart;
        });
      } else {
        setErrorMessage(data.message);
      }
    } catch (error) {
      console.error('Error deleting item:', error);
      setErrorMessage('Failed to remove item');
    }
  };

  const handleMarkServe = async (orderId) => {
    //setQueue(queue.filter((item) => item.order_id !== orderId));
    // Optionally send update to backend
    try {
      const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/update-order-status-serve`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          order_id: orderDetails.order_id,
          menu_item_id: orderId,
        }),
      });

      const data = await response.json();
      if (data.success) {
        fetchOrderlist(orderDetails.order_id)
      } else {
        setErrorMessage(data.message);
      }
    } catch (err) {
      console.error('Error updating table:', err);
      setErrorMessage('Failed to update table');
    }
  };

  const handleMarkDone = async (Id, menuItemId) => {
    //setQueue(queue.filter((item) => item.order_id !== orderId));
    // Optionally send update to backend
    try {
      const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/update-order-status-done`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          id: Id,
          menu_item_id: menuItemId,
        }),
      });
      const data = await response.json();
      if (data.success) {
        fetchQueuelist();
      } else {
        setErrorMessage(data.message);
      }
    } catch (err) {
      console.error('Error updating table:', err);
      setErrorMessage('Failed to update table');
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

  const sendToKitchen = async() => {
    setIsLoadingSend(true);
    try {

      const items = cart.map((item) => ({
        menu_item_id: item.menu_id,
        note: orderNotes[item.menu_id] || '',
      }));


      const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/send-to-kitchen`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          order_id: orderDetails.order_id,
          items,
        }),
      });


      const data = await response.json();
      if (data.success) {

        fetchOrderDetails(orderDetails.order_id);
        alert('sent to kitchen')
      } else {
        setErrorMessage(data.message);
      }
    } catch (err) {
      console.error('Error sending to kitchen:', err);
      setErrorMessage('Failed sending to kitchen');
    } finally {
       setIsLoadingSend(false);
    }

  };
  
  const handlePaymentConfirmed = async (method, cash = null, change = null) => {
    setIsLoadingPayment(true);
    // continue logic: save payment, clear cart, etc.
    try {
      const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/save-payment`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          order_id: orderDetails.order_id,
          total_amount: (calculateTotal() - (discount?.value || 0)).toFixed(2),
          /*discount_total: (calculateTotal() * (discount?.value )).toFixed(2),*/
          discount_total: (discount?.value || 0).toFixed(2),
          discounted_amount: (calculateTotal() - (discount?.value || 0)).toFixed(2),
          user_id: user.id,
        }),
      });

      const data = await response.json();
      if (data.success) {
        setOrderDetails({
          order_id: '',
          user_id: user.id,
          table_id: '',
          menu_item_id: '',
          quantity: 1,
          order_type: '',
  
        });
        setCart([]);
        fetchPendingOrders();
        
        alert(`Payment successful!\nAmount: ₱${(calculateTotal() - (discount?.value || 0)).toFixed(2)}\nDiscount: ${(discount?.value || 0).toFixed(2)}`);
        
      } else {
        setErrorMessage(data.message);
      }
    } catch (err) {
      console.error('Error updating quantity:', err);
      setErrorMessage('Failed to update quantity');
    } finally {
      setIsLoadingPayment(false);
      setIsPaymentModalOpen(false);
    }

  };

  useEffect(() => {
    fetchQueuelist();
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

    const timer = setInterval(() => {
      setCurrentTime(new Date());
    }, 1000);

  }, []);

  return (
    <div className="h-screen overflow-hidden flex flex-col">
      {/* Top Navigation */}
      <div className="bg-[#2B4F4B] flex items-center">
        {/* Mobile Cart Toggle */}
        <button
          className="lg:hidden text-white flex space-x-2 relative"
          onClick={() => setIsMobileCartOpen(!isMobileCartOpen)}
          hidden={!isFormOpen}
        >
          <ViewColumnsIcon className="h-6 w-6 ml-3" />
          {cart.length > 0 && (
            <span className="top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 text-xs flex items-center justify-center">
              {cart.length}
            </span>
          )}
        </button>
        <div className="flex items-center justify-between p-4 gap-4 text-white"
          hidden={isFormOpen}>
          <button onClick={() => fetchQueuelist()}>
            <ArrowPathIcon className="h-6 w-6" />
          </button>
          <div className="italic text-white text-xs">
            last refresh {lastRefreshTime || 'N/A'}
          </div>
        </div>
        <Menu as="div" className="w-full flex justify-end">
          <MenuButton as="div" className="gap-2 p-2">
            <span className="flex items-center gap-2 hover:border-l-2 text-white">
              <span className="min-w-0 text-end">
                <span className="block truncate text-sm/5 font-medium">
                  {user?.name}
                </span>
                <span className="block truncate text-xs/5 font-normal">
                  {user?.role}
                </span>
              </span>
              <img
                className="h-10 w-10 rounded-full object-cover"
                src="assets/RENZIE.png"
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
                <a className="flex items-center gap-2 px-2 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  onClick={() => { setIsFormOpen(false); fetchQueuelist(); }}
                  hidden={!isFormOpen}>
                  <QueueListIcon className="size-5 text-gray-600" />
                  Drinks Queue
                </a>
              </MenuItem>

              <MenuItem>
                <a className="flex items-center gap-2 px-2 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  onClick={() => setIsFormOpen(true)}
                  hidden={isFormOpen}>
                  <QueueListIcon className="size-5 text-gray-600" />
                  Order Take
                </a>
              </MenuItem>

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
      {isFormOpen && (
        <div className="flex-1 flex flex-col lg:flex-row">
          {/* Categories - Horizontal scroll on mobile */}
          <div className="lg:w-1/6 bg-gray-800 p-4 overflow-x-auto lg:overflow-y-auto flex flex-col lg:flex-col gap-2 ">
            <div className="flex lg:flex-col gap-2 min-w-max">
              { isLoadingCategoryList ? (
                <div className='text-center w-full text-white'>fetching all categories..</div>
              ) : (
                <>
                  <button className={`p-3 lg:p-2 rounded whitespace-nowrap ${activeCategory === 'all'
                    ? 'bg-white text-dark'
                    : 'bg-gray-700 text-white hover:bg-gray-600'
                  }`}
                  onClick={() => setActiveCategory('all')}>
                    ALL
                  </button>
                  {CategoryItems.map(category => (
                    <button
                      key={category.name}
                      className={`p-3 lg:p-2 rounded whitespace-nowrap ${activeCategory === category.name
                          ? 'bg-white text-dark'
                          : 'bg-gray-700 text-white hover:bg-white hover:text-black'
                        }`}
                      onClick={() => setActiveCategory(category.name)}
                    >
                      {category.name}
                    </button>
                  ))}
                </>
              )}
             
            </div>
          </div>

          {/* Menu Items Grid - Responsive columns */}
          <div className="p-4 flex-1 lg:w-5/6 bg-gray-100">
            {/* Mobile Search */}
            <input
              type="text"
              placeholder="Search menu..."
              className="w-full p-2 rounded mb-4 border-1"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
            />

            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 overflow-y-auto max-h-[calc(100vh-150px)]">
              { isLoadingMenuItems ? (
                <div className='text-center w-full col-span-4'>fetching menu items..</div>
              ) : (
                 
              filteredItems.map(item => {
                const isOut = item.is_available == 0;
                const isOrderPaid = orderDetails.order_status === 'paid';
                const isDisabled = isOut || isOrderPaid;
                const isLoading = loadingItemId === item.id;

                return (
                  <button
                    key={item.id}
                    className={`p-1 rounded-lg border flex flex-col w-full
                      ${isDisabled
                        ? 'border-red-400 text-red-400 opacity-50 cursor-not-allowed'
                        : 'bg-white border hover:shadow-md text-black'}`}
                    onClick={() => {
                      if (isDisabled) {
                        alert('This item is not available or the order is already paid.');
                      } else {
                        setLoadingItemId(item.id);
                        addToCart(item).finally(() => {
                          setLoadingItemId(null);
                        });
                      }
                    }}
                    disabled={isDisabled}
                  >
                    <div className="h-24 md:h-32 bg-gray-200 rounded-md">
                      <img
                      src={item.image_url}
                      alt={item.name}
                      className="w-full h-24 md:h-32 object-cover rounded mb-2"
                      />
                    </div>
                    {isLoading ? (
                      'Adding...'
                    ) : (
                      <>
                        <p className="font-bold text-sm md:text-sm">{item.name}</p>
                        <p className="text-gray-600">₱{item.price.toFixed(2)}</p>
                      </>
                    )}
                  </button>
                );
              }) )}


            </div>
          </div>

          {/* Cart - Slide in from right on mobile */}
          <div className={`
          fixed lg:relative right-0 top-0 h-screen w-full lg:w-1/3 
          bg-white shadow-lg transform transition-transform duration-300 ease-in-out
          ${isMobileCartOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'}
          z-50 lg:z-0
        `}>
            {/* Mobile Cart Header */}
            <div className="lg:hidden flex items-center justify-between p-4 bg-gray-800 text-white">
              <button onClick={() => fetchOrderDetails(orderDetails.order_id)}>
                <ArrowPathIcon className="h-6 w-6" />
              </button>
              <h2>Subtotal: ₱{calculateTotal().toFixed(2)}</h2>
              <button onClick={() => setIsMobileCartOpen(false)}>
                <XMarkIcon className="h-6 w-6" />
              </button>
            </div>

            {/* Cart Content */}
            <div className="flex flex-col h-full">
              {/* Order Type & Table */}
              <div className="grid grid-cols-3 gap-2 border-b p-2">

                <select
                  className="p-2 border rounded"
                  value={orderDetails.order_id}
                  onChange={(e) => {
                    const orderId = e.target.value;
                    if (orderId === "") {
                      setOrderDetails({
                        order_id: '',
                        user_id: user.id,
                        table_id: '',
                        menu_item_id: '',
                        quantity: 1,
                        order_type: '',
                        
                      });
                      setCart([]); // Optionally clear cart
                    } else {
                      fetchOrderDetails(orderId);
                    }
                  }}
                >
                  <option value="" selected disabled
                  >Select Orders</option>
                  <option value="">New Order</option>

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
                  className="p-2 border rounded"
                  value={orderDetails.order_type}
                  onChange={(e) => setOrderDetails({...orderDetails, order_type: e.target.value })}
                  hidden={orderDetails.order_id !== ""}
                >
                  <option value="" selected disabled hidden>Order type</option>
                  <option value="dine-in">Dine-in</option>
                  <option value="take-out">Take-out</option>
                  <option value="delivery">Delivery</option>
                </select>

                {orderDetails.order_type === 'dine-in' && (
                  <select
                    className="p-2 border rounded"
                    value={orderDetails.table_id}
                    onChange={(e) => setOrderDetails({...orderDetails, table_id: Number(e.target.value) })}
                  >
                    <option value="" selected disabled hidden>Table #</option>
                    {vacantTables.map((item, index) => (
                      <option key={index} value={item.id}>
                        {item.name}
                      </option>
                    ))}
                  </select>
                )}
                <div className='col-span-1 flex items-center gap-2'>
                  <button
                    className="p-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                    hidden={!orderDetails.order_id ||
                      !orderDetails.table_id ||
                      orderDetails.table_id === tableNumber}
                    onClick={() => {
                      // Call your update API here
                      updateOrderTable();
                    }}
                  >
                    Update Table
                  </button>

                  <button className='hidden md:block p-2 rounded w-min bg-gray-200 hover:bg-[#2B4F4B] hover:text-white'
                    onClick={() => fetchOrderDetails(orderDetails.order_id)}>
                    <ArrowPathIcon className="h-6 w-6" />
                  </button>
                </div>

              </div>

              {/* Cart Items */}
              <div className="p-2 overflow-y-auto h-screen max-h-[calc(100vh-150px)] mb-15">
                {isLoadingOrderList ? (
                  <div className='text-center w-full'>fetching cart items..</div>
                ) : (
                  cart.map(item => {
                  const isUpdatingServe = loadingServerId === item.order_id;
                  return (
                 <div key={item.order_id} className="mb-1 p-1 border-b border-gray-200">
                    <div className="flex justify-between">
                      <div className='flex gap-3 grow items-center'>
                        {/* Remove Button */}
                        <div className="w-9 flex-none">
                          <button
                            className="px-3 py-1 bg-red-500 rounded text-lg text-white"
                            onClick={() => removeFromCart(item.order_id, item.menu_id)}
                            hidden={['ready', 'served'].includes(item.status)}
                          >
                            x
                          </button>
                        </div>

                        {/* Item Info */}
                        <div className='grow text-left'>
                          <p className="font-bold text-base">{item.name}</p>
                          <p className="text-gray-600">
                            ₱{item.price.toFixed(2)} <strong>X {item.quantity}</strong>
                          </p>
                        </div>

                        {/* Quantity Controls */}
                      
                      </div>

                      {/* Status and Serve Button */}
                      <div className='flex items-center gap-1'
                        hidden={item.status === 'queued'}>
                        <p className={
                          item.status === 'queued' ? 'text-black-500' :
                          item.status === 'preparing' ? 'text-yellow-500' :
                          item.status === 'ready' ? 'text-blue-500' :
                          item.status === 'served' ? 'text-green-500' :
                          'text-gray-500'
                        }>
                          {item.status}
                        </p>
                        <button
                          hidden={['preparing', 'queued', 'served'].includes(item.status)}
                          onClick={() => {
                            setLoadingServerId(item.order_id);
                            handleMarkServe(item.order_id).finally(() => {
                              setLoadingServerId(null);
                            });
                          }}
                          className="p-2 bg-green-500 rounded hover:bg-blue-700 text-white text-sm"
                        >
                          {isUpdatingServe ? 'Updating...' : 'Serve'}
                        </button>
                      </div>
                    </div>

                    {/* Notes Input */}
                    <div className="flex gap-2 my-3"
                    hidden={['preparing', 'ready', 'served'].includes(item.status)}>
                      <input
                        type="text"
                        placeholder="Add note then send to kitchen..."
                        className="flex-1 p-2 text-xs border rounded border-gray-200"
                        value={orderNotes[item.menu_id] || ''}
                        onChange={(e) => addNote(item.menu_id, e.target.value)}
                      />
                      <div
                          className="flex items-center gap-2 w-auto flex-none"
                        >
                          <button
                            className="px-3 py-1 bg-gray-200 rounded text-lg"
                            onClick={() => updateQuantity(item.order_id, item.quantity - 1)}
                          >-</button>
                          <span>{item.quantity}</span>
                          <button
                            className="px-3 py-1 bg-gray-200 rounded text-lg"
                            onClick={() => updateQuantity(item.order_id, item.quantity + 1)}
                          >+</button>
                        </div>
                    </div>
                  </div>

                );
                }))}
              </div>

              {/* Order Actions */}
              <div className='p-1 bg-gray-50 sticky bottom-5'
                hidden={
                  cart.length === 0 || orderDetails.order_status === 'paid'}
              >
                <div className="p-2 bg-gray-50">
                  <div className='w-full text-end'
                    hidden={
                      cart.some(item => ['preparing', 'ready'].includes(item.status))
                    }
                    >
                    Sub Total: ₱ {calculateTotal().toFixed(2)}
                  </div>
                  <select
                    hidden
                    className="p-2 border rounded w-full"
                    value={discount ? JSON.stringify(discount) : ""}
                    onChange={(e) => {
                      const value = e.target.value;
                      if (value === "") {
                        setDiscount(null); // clear the value
                      } else {
                        setDiscount(JSON.parse(value));
                      }
                    }}
                  >
                    <option value="">Select Discount</option>
                    {discounts.map((item) => (
                      <option key={item.id} value={JSON.stringify(item)}>
                        {item.name}
                      </option>
                    ))}
                  </select>
                  <input
                  type="number"
                  hidden={cart.some(item =>
                    ['queued', 'preparing', 'ready'].includes(item.status)
                  )}
                  className="p-2 border rounded w-full"
                  value={discount?.value ?? ""}
                  placeholder="Discount amount"
                  onChange={(e) => {
                    const value = e.target.value;
                    if (value === "") {
                      setDiscount(null); // clear discount
                    } else {
                      setDiscount({ value: Number(value) }); // set as object with value key
                    }
                  }}
                />

                  


                </div>
                <div className="p-2 bg-gray-50  flex flex-row gap-2">
                  <button
                    className="w-full bg-yellow-500 text-white py-2 rounded hover:bg-blue-600"
                    onClick={() => { sendToKitchen(); }}
                    hidden={!cart.some(item => item.status === 'queued')}

                  >
                  {isLoadingSend ? 'Sending...' : 'Send to Kitchen'}
                  </button>
                  <button
                    hidden={
                      cart.some(item => ['queued','preparing', 'ready',].includes(item.status))
                    }
                    className="w-full bg-green-500 text-white py-2 rounded hover:bg-blue-600"
                    onClick={() => setIsPaymentModalOpen(true)}
                  >Pay ₱ {(calculateTotal() - (discount?.value || 0)).toFixed(2)}
                  </button>
                  <PaymentModal
                    isOpen={isPaymentModalOpen}
                    totalAmount={Number(calculateTotal() - (discount?.value || 0))}
                    onClose={() => setIsPaymentModalOpen(false)}
                    onPaymentConfirmed={handlePaymentConfirmed}
                    isLoadingPayment={isLoadingPayment}
                  />

                </div>
              </div>
            </div>
          </div>
        </div>
      )}
      {!isFormOpen && (
        
        <div className="p-4 flex-1 bg-gray-100 overflow-y-auto">
          <div className="text-center border-b-1 p-1">
            <div className="text-l text-gray-600">
              {currentTime.toLocaleDateString([], {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
              })}
            </div>
            <div className="text-lg font-bold text-blue-500">
              {currentTime.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
              })}
            </div>

          </div>
          <div className='m-2 text-l text-center'>
            Beverages Queue / Todo
          </div>
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {  isLoadingDrinkQueue ? (
            <p className="text-gray-500 text-center col-end-2">Loading Drinks Queue...</p>
          ) : (
            queue.map((item) => {
          const isLoadingDone = loadingDoneItemId === item.id;

          return (
              <div
                key={item.id}
                className={`rounded-lg shadow p-4 space-y-2 border ${item.status === 'queued'
                    ? 'bg-gray-50 text-gray-300 border-gray-300'
                    : 'bg-white text-black border'
                  }`}>
                <div className={`grid grid-cols-2 text-xs${item.status === 'queued'
                    ? 'text-gray-300'
                    : 'text-red-500'
                  }`}>
                  <div>
                    {item.identifier}
                  </div>
                  <div className='text-end'>
                    {dayjs.tz(item.updated_at).fromNow()}
                  </div>
                </div>
                <div className="text-start">{item.quantity} - {item.name} </div>
                <div className="text-s text-red-500 italic"
                  hidden={!item.note}
                >Notes: {item.note}</div>
                <button
                  hidden={['queued', 'served'].includes(item.status)}
                  onClick={() => {
                    
                    setLoadingDoneItemId(item.id);
                    handleMarkDone(item.id,item.menu_item_id).finally(() => {
                      setLoadingDoneItemId(null);
                    });
                  }}
                  className="mt-2 bg-green-600 hover:bg-green-700 text-white text-sm py-2 px-3 rounded"
                >
                  {isLoadingDone ? 'Removing...' : 'Done Preparing'}
                </button>
              </div>
            )
}))}
          </div>
        </div>
      )}
    </div>
  );
}

export default CashierUi;