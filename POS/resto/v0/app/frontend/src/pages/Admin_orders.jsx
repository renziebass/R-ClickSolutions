import { MagnifyingGlassIcon } from "@heroicons/react/24/outline";
import React, { useState, useRef} from "react";
import { useEffect } from "react";
import { filterOrders } from "../utils/filterOrders";
import {
    ArrowPathIcon,
    PrinterIcon,
    XMarkIcon,
  } from "@heroicons/react/24/outline";
import.meta.env.VITE_API_BASE_URL
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import relativeTime from 'dayjs/plugin/relativeTime';
import { useRequireAuth } from '../contexts/AuthContext';

dayjs.extend(utc);
dayjs.extend(timezone);
dayjs.extend(relativeTime);



const Admin_orders = () => {
const [isFormOpen, setIsFormOpen] = useState(true);
const [formOrder, setFormOrder] = useState([]);
const [cart, setCart] = useState([]);
const [orderDetails, setOrderDetails] = useState([]);
const [errorMessage, setErrorMessage] = useState('');
const [filters, setFilters] = useState({
  dateRange: "today",
  search: "",
  orderType: "all",
  status: "all",
  paymentMethod: "all",
  customFrom: "",
  customTo: "",
});

const [currentTime, setCurrentTime] = useState(new Date());
const [isLoading, setIsLoading] = useState(false);
const [orders, setOrders] = useState([]);
const filteredOrders = filterOrders(orders, filters);
const printRef = useRef();
const user = useRequireAuth();

const handlePrint = () => {
alert('Printing is disabled for this demo. Contact Mr. Renzie Operario Bassig @09772332632 for more information.');
/*const printContents = printRef.current.innerHTML;
const originalContents = document.body.innerHTML;

document.body.innerHTML = printContents;
window.print();
document.body.innerHTML = originalContents;
window.location.reload();
*/
};

// fetch orders
const fetchOrders = async () => {
  setIsLoading(true);
  try {
    const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/fetch-admin-orders?company_id=${user?.company_id}`);
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

// fetch order details 
const fetchOrderDetails = async (orderId) => {

    try {
      const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/order-details?order_id=${orderId}&company_id=${user?.company_id}`);
      const data = await response.json();

    if (data.success) {
      const order = data.data[0];

      setOrderDetails(order);
      
      setErrorMessage('');
    } else {
      setErrorMessage(data.message);
    }
  } catch (error) {
    setErrorMessage('Error fetching orders');
    console.error('Error:', error);
  } finally {
    fetchOrderlist(orderId);
  }
};

const fetchOrderlist = async (orderId) => {

  try {
    const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/order-items?order_id=${orderId}&company_id=${user?.company_id}`);
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

  }
};

const handleOpenOrder = (orderId) => {
  setIsFormOpen(false);
  setFormOrder(orderId);
  fetchOrderDetails(orderId.order_id);
  };

useEffect(() => {
  fetchOrders();
}, []);
  return (
    <>
    {isFormOpen && (
    <div>
    <div className="p-4 space-y-4">
      <div className='grid grid-cols-2'>
        <h1 className="text-l">Filter Orders</h1>
        <div className="flex justify-end">
          <button className="p-2 bg-gray-200 rounded hover:bg-[#2B4F4B] hover:text-white" onClick={handlePrint}><PrinterIcon className="size-5" /></button>
        </div>
      </div>

      <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-1 text-sm">
        <div className="col-span-2">
          <div className="flex rounded border">
            <input
              type="text"
              placeholder="Search Order ID, Table #, Customer..."
              className="px-3 py-2 w-full text-sm outline-none"
              value={filters.search}
              onChange={(e) => setFilters({ ...filters, search: e.target.value })}
              title="Search by Order ID, Table number, or Customer Name"
            />
         
          </div>
        </div>

        <select
  className="border rounded px-3 py-2"
  value={filters.dateRange}
  onChange={(e) => setFilters({ ...filters, dateRange: e.target.value })}
  title="Filter orders by date created"
>
  <option value="all">All</option>
  <option value="today">Today</option>
  <option value="yesterday">Yesterday</option>
  <option value="this_week">This Week</option>
  <option value="custom">Custom Range</option>
</select>

{filters.dateRange === "custom" && (
  <>
    <input
      type="date"
      className="border rounded px-3 py-2"
      value={filters.customFrom}
      onChange={(e) => setFilters({ ...filters, customFrom: e.target.value })}
      title="From date"
    />
    <input
      type="date"
      className="border rounded px-3 py-2"
      value={filters.customTo}
      onChange={(e) => setFilters({ ...filters, customTo: e.target.value })}
      title="To date"
    />
  </>
)}



        <select
          className="border rounded px-3 py-2"
          value={filters.orderType}
          onChange={(e) => setFilters({ ...filters, orderType: e.target.value })}
          title="Filter by order type"
        >
          <option value="all">All Types</option>
          <option value="dine-in">Dine-in</option>
          <option value="take-out">Take-out</option>
          <option value="delivery">Delivery</option>
        </select>

        <select
          className="border rounded px-3 py-2"
          value={filters.status}
          onChange={(e) => setFilters({ ...filters, status: e.target.value })}
          title="Order status (pending, paid, void, etc.)"
        >
          <option value="all">All Status</option>
          <option value="pending">Pending</option>
          <option value="in_progress">In Progress</option>
          <option value="paid">Paid</option>
          <option value="void">Void</option>
        </select>

        <select
          className="border rounded px-3 py-2"
          value={filters.paymentMethod}
          onChange={(e) => setFilters({ ...filters, paymentMethod: e.target.value })}
          title="Filter by payment method"
        >
          <option value="all">All Methods</option>
          <option value="cash">Cash</option>
          <option value="card">Card</option>
          <option value="gcash">GCash</option>
          <option value="bank">Bank Transfer</option>
        </select>

      </div>
    </div>

    <div className="p-4 space-y-4 mt-3" ref={printRef}>
          <div className='grid grid-cols-2 print:hidden'>
            <h1 className="text-l">Filter Orders</h1>
            <div className="flex justify-end">
               <button
                  onClick={fetchOrders}
                  className="p-2 bg-gray-200 rounded hover:bg-[#2B4F4B] hover:text-white"
                >
                  <ArrowPathIcon className="size-5" />
                </button>
            </div>
          </div>
         
          {errorMessage && (
    <div className="text-red-600 text-sm bg-red-100 border border-red-200 p-2 rounded mb-2">
      {errorMessage}
    </div>
  )}  
            <div className="hidden print:block text-xs text-gray-600 mb-4 space-y-1 text-center">
              <p className="text-lg">Orders List</p>
              <p className="text-l">{currentTime.toLocaleString()}</p>
              <p><strong>Date Range:</strong> {filters.dateRange === 'custom' ? `${filters.customFrom} to ${filters.customTo}` : filters.dateRange}</p>
              <p><strong>Order Type:</strong> {filters.orderType}</p>
              <p><strong>Status:</strong> {filters.status}</p>
              <p><strong>Payment Method:</strong> {filters.paymentMethod}</p>
              {filters.search && <p><strong>Search:</strong> "{filters.search}"</p>}
            </div>
            <table className="table-auto min-w-full w-full text-xs md:text-sm text-left">
                <thead className="">
                    <tr className="">
                    <th className="p-2 hidden sm:table-cell">Order ID</th>
                    <th className="p-2">Table/Type</th>
                    <th className="p-2 hidden sm:table-cell">Items</th>
                    <th className="p-2">Total</th>
                    <th className="p-2 hidden md:table-cell">Discount</th>
                    <th className="p-2">Status</th>
                    <th className="p-2">Date/Time</th>
                    <th className=""></th>
                    </tr>
                </thead>
               <tbody>
                {isLoading ? (
                  <tr>
                    <td colSpan="7" className="text-center py-4">Loading orders...</td>
                  </tr>
                ) : orders.length === 0 ? (
                  <tr>
                    <td colSpan="7" className="text-center py-4">No orders found.</td>
                  </tr>
                ) : (
                  filteredOrders.map((order) => (
                    <tr key={order.order_id} className="border-t border-gray-100 text-gray-500 hover:bg-gray-200"
                    onClick={() => handleOpenOrder(order)}>
                      <td className="p-2 hidden md:table-cell">{order.order_id}</td>
                      <td className="p-2">
                        {order.table_id ? `Table ${order.table_id}` : order.type}
                      </td>
                      <td className="p-2 hidden sm:table-cell">{order.items || '-'}</td>
                      <td className="p-2">
                        ₱{
                        (Number(order.total_amount)).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                        })
                        }
                      </td>
                      <td className="p-2 hidden sm:table-cell">
                        ₱{
                        (Number(order.discount_total)).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                        })
                        }
                      </td>
                      <td className="p-2">{order.status}</td>
                      <td className="p-2">{dayjs.utc(order.created_at).tz('Asia/Manila').format('MM/DD/YYYY - HH:mm:ss')}</td>
                      <td>
                        <button
                        hidden={order.status === 'paid' || order.items >= 0}
                        className=" bg-red-500 rounded text-white hover:bg-[#2B4F4B] hover:text-white">
                          <XMarkIcon className="size-5"/>
                        </button>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>

            </table>
    </div>
    </div>
    )}
    {!isFormOpen && (
    <div className="p-4 space-y-4">
      <div>
        <div className='grid grid-cols-2'>
          
          <h1 className="text-l">Order Details</h1>
          
          <div className="flex justify-end gap-2">
            <button
            className="p-2 bg-red-500 rounded text-white hover:bg-[#2B4F4B] hover:text-white">
              <XMarkIcon className="size-5" onClick={() => setIsFormOpen(true)} />
            </button>
             <button
                  onClick={() => {
                    fetchOrderDetails(orderDetails.id);
                  }}
                  className="p-2 bg-gray-200 rounded hover:bg-[#2B4F4B] hover:text-white"
                >
                  <ArrowPathIcon className="size-5" />
                </button>
            <button className="p-2 bg-gray-200 rounded hover:bg-[#2B4F4B] hover:text-white" onClick={handlePrint}><PrinterIcon className="size-5" /></button>
          </div>
        </div>
            <div className="py-2 text-center">
              <div className="grid grid-cols-3 text-center mt-3">
                <div>
                  <p>{dayjs.utc(orderDetails.created_at).tz('Asia/Manila').format('MM/DD/YYYY - HH:mm:ss')}</p>
                  <p className="text-xs text-gray-500">Date/Time</p>
                </div>
                <div>
                  <p>{orderDetails?.id ?? ''}</p>
                  <p className="text-xs text-gray-500">Order ID</p>
                </div>
                <div>
                  <p>{orderDetails?.type ?? ''}</p>
                  <p className="text-xs text-gray-500">Type</p>
                </div>
                <div>
                  <p>{orderDetails?.status ?? ''}</p>
                  <p className="text-xs text-gray-500">Status</p>
                </div>
                <div>
                  <p>  ₱
                  {(Number(orderDetails?.total_amount ?? 0)).toLocaleString('en-US', {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2
                  })}</p>
                  <p className="text-xs text-gray-500">Total</p>
                </div>
                <div>
                  <p>  ₱
                  {(Number(orderDetails?.discount_total ?? 0)).toLocaleString('en-US', {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2
                  })}</p>
                  <p className="text-xs text-gray-500">Discount</p>
                </div>

              </div>
            </div>
      </div>
      <div>
        <h1 className="text-l">Order List</h1>
            <div className="flex justify-end mt-3">
              <table className="table-auto min-w-full w-full text-xs md:text-sm text-left">
                <thead className="">
                    <tr className="">
                    <th className="p-2">Time</th>
                    <th className="p-2">QTY</th>
                    <th className="p-2">Name</th>
                    <th className="p-2">Total</th>
                    <th className="p-2">Discount</th>
                    <th className="p-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                {cart.map((order, idx) => (
                  <tr key={order.id || idx}>
                    <td className="p-2">{order.updated_at ? dayjs.utc(order.updated_at).tz('Asia/Manila').format('HH:mm:ss') : '-'}</td>
                    <td className="p-2">{order.quantity || '-'}</td>
                    <td className="p-2">{order.name || '-'}</td>
                    <td className="p-2">
                      ₱{(Number(order.quantity * order.price || 0)).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                      })}
                    </td>
                    <td className="p-2">
                      ₱{(Number(order.discount_total || 0)).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                      })}
                    </td>
                    <td className="p-2">{order.status || '-'}</td>
                  </tr>
                ))}
                </tbody>
                </table>
            </div>
            <div>
              
            </div>
      </div>

    </div>
    )}
    </>
  );
};

export default Admin_orders