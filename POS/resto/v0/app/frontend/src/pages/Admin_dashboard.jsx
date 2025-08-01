import React, { useState, useRef, useEffect } from 'react';
import ReactToPrint from 'react-to-print';
import {
    PrinterIcon
  } from "@heroicons/react/24/outline";
import.meta.env.VITE_API_BASE_URL;
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(utc);
dayjs.extend(timezone);
dayjs.extend(relativeTime);

function Admin_dashboard(){

const [currentTime, setCurrentTime] = useState(new Date());
const [errorMessage, setErrorMessage] = useState('');
const [salesData1, setSalesData1] = useState([]);
const [topSellingItem, setTopSellingItem] = useState([]);
const [pendingNumbers, setPendingNumbers] = useState([]);
const [menuItemsSummary, setMenuItemsSummary] = useState([]);
const [kitchenQueue, setKitchenQueue] = useState([]);
const [drinksQueue, setDrinksQueue] = useState([]);
const printRef = useRef();

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

const fetchSalesData1 = async () => {
  
  try {
    const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/fetch-sales-data1`);
    const data = await response.json();
    if (data.success) {
      setSalesData1(data.data[0]);
      setErrorMessage('');
    } else {
      setErrorMessage(data.message);
    }
  } catch (error) {
    setErrorMessage('Error fetching Sales Data');
    console.error('Error:', error);
  } finally {
 
  }
};

const fetchTopSellingItem = async () => {
  
  try {
    const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/fetch-top-selling-item`);
    const data = await response.json();
    if (data.success) {
      setTopSellingItem(data.data[0]);
      setErrorMessage('');
    } else {
      setErrorMessage(data.message);
    }
  } catch (error) {
    setErrorMessage('Error fetching Sales Data');
    console.error('Error:', error);
  } finally {
 
  }
};

const fetchPendingNumbers = async () => {
  
  try {
    const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/fetch-pending-numbers`);
    const data = await response.json();
    if (data.success) {
      setPendingNumbers(data.data[0]);
      setErrorMessage('');
    } else {
      setErrorMessage(data.message);
    }
  } catch (error) {
    setErrorMessage('Error fetching Data');
    console.error('Error:', error);
  } finally {
 
  }
};

const fetchMenuItemsSummary = async () => {
  
  try {
    const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/fetch-menu-items-summary`);
    const data = await response.json();
    if (data.success) {
      setMenuItemsSummary(data.data);
      setErrorMessage('');
    } else {
      setErrorMessage(data.message);
    }
  } catch (error) {
    setErrorMessage('Error fetching Data');
    console.error('Error:', error);
  } finally {
 
  }
};

const fetchKitchenQueue = async () => {
  
  try {
    const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/fetch-kitchen-queue`);
    const data = await response.json();
    if (data.success) {
      setKitchenQueue(data.data);
      setErrorMessage('');
    } else {
      setErrorMessage(data.message);
    }
  } catch (error) {
    setErrorMessage('Error fetching Data');
    console.error('Error:', error);
  } finally {
 
  }
};

const fetchDrinksQueue = async () => {
  
  try {
    const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/fetch-drinks-queue`);
    const data = await response.json();
    if (data.success) {
      setDrinksQueue(data.data);
      setErrorMessage('');
    } else {
      setErrorMessage(data.message);
    }
  } catch (error) {
    setErrorMessage('Error fetching Data');
    console.error('Error:', error);
  } finally {
 
  }
};




useEffect(() => {
  fetchSalesData1();
  fetchTopSellingItem();
  fetchPendingNumbers();
  fetchKitchenQueue();
  fetchDrinksQueue();
  fetchMenuItemsSummary();
}, []);

    return (
        <div className="p-4" >
          <div className='grid grid-cols-2'>
            <h1 className="text-l">Admin Dashboard</h1>
            <div className="flex justify-end">
              <button className="p-2 bg-gray-200 rounded hover:bg-[#2B4F4B] hover:text-white" onClick={handlePrint}><PrinterIcon className="size-5" /></button>
            </div>
          </div>
          
          <div ref={printRef}>
          <h2 className='text-center my-3'>{currentTime.toLocaleString()}</h2>
          {/* KPI Cards */}
          <div className="grid grid-cols-2 md:grid-cols-3 gap-2 mb-5">

            <div className="border-t-1 border-gray-300 py-2 text-center">
              <p>{salesData1?.total_orders ?? 0} <span>Orders</span></p>
              <div className="grid grid-cols-3 text-center">
                <div>
                  <p>{salesData1?.dine_in ?? 0}</p>
                  <p className="text-xs">Dine-in</p>
                  
                </div>
                <div>
                  <p>{salesData1?.take_out ?? 0}</p>
                  <p className="text-xs">Take-out</p>
                </div>
                <div>
                  <p>{salesData1?.delivery ?? 0}</p>
                  <p className="text-xs">Delivery</p>
                </div>

              </div>
            </div>

            <div className="border-t-1 border-gray-300 py-2">
              <div className="grid grid-cols-2 text-sm">
                <p className="text-end">
                  ₱ 
                  {(Number(salesData1?.gross_sales ?? 0)).toLocaleString('en-US', {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2
                  })}
                </p>
                <p className="text-gray-500 text-start ml-2"> Gross</p>
              </div>
              <div className="grid grid-cols-2 text-sm">
                <p className="text-end">
                  ₱
                  {(Number(salesData1?.total_discounts ?? 0)).toLocaleString('en-US', {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2
                  })}
                  </p>
                <p className="text-gray-500 text-start ml-2"> Discounts</p>
              </div>
              <div className="grid grid-cols-2 text-sm">
                <p className="text-end">
                  ₱
                  {(Number(salesData1?.net_sales ?? 0)).toLocaleString('en-US', {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2
                  })}
                  </p>
                <p className="text-gray-500 text-start ml-2"> Net</p>
              </div>
            </div>

            <div className="border-t-1 border-gray-300 py-2 text-center col-span-2 md:col-span-1 ">
             <div className="grid grid-cols-2">
              <div>
                <p className="text-l text-blue-600">{topSellingItem?.name ?? 'N/A'}</p>
                <p className="text-gray-500 text-xs">Top Selling Menu</p>
                
              </div>
              <div>
                <p className="text-l text-red-600">{pendingNumbers?.pending_orders ?? 0}</p>
                <p className="text-gray-500 text-xs">Orders in Progress</p>
              </div>
             </div>
            </div>
    
            
          </div>

          {/* Menu items summary */}
          <div className="border-t-1 border-gray-300 py-4">
            <p className="text-l mb-4">Menu Item Sales Summary</p>
            <ul className="space-y-4">
              {menuItemsSummary.map((item) => {
                return (
                  <li key={item.menu_item} className="flex justify-between items-center text-sm text-gray-500">
                    <span className="text-start">{item.menu_item}</span>
                    <span className="text-end">
                      {item.total_quantity} order(s), ₱ {
                      (Number(item.total_revenue)).toLocaleString('en-US', {
                      minimumFractionDigits: 2,
                      maximumFractionDigits: 2
                      })
                      }
                    </span>
                  </li>
                );
              })}
            </ul>
          </div>
    
          {/* Kitchen Queue List */}
          <div className="border-t-1 border-gray-300 py-4">
            <p className="text-l mb-4">Kitchen Queue</p>
            <ul className="space-y-4">
              {kitchenQueue.map((item) => {

                return (
                  <li key={item.updated_at} className="flex justify-between items-center text-sm text-gray-500">
                    <span className="text-start">{item.identifier}: {item.menu_item_name}</span>
                    <span className="text-end">
                      {dayjs(item.updated_at).tz('Asia/Manila').fromNow()}
                    </span>
                  </li>
                );
              })}
            </ul>
          </div>
    
          {/* Drinks Queue List */}
          <div className="border-t-1 border-gray-300 py-4">
            <p className="text-l mb-4">Drinks Queue</p>
           <ul className="space-y-4">
             {drinksQueue.map((item) => {

                return (
                  <li key={item.updated_at} className="flex justify-between items-center text-sm text-gray-500">
                    <span className="text-start">{item.identifier}: {item.menu_item_name}</span>
                    <span className="text-end">
                      {dayjs(item.updated_at).tz('Asia/Manila').fromNow()}
                    </span>
                  </li>
                );
              })}
            </ul>
          </div>
          
        </div>
        </div>
      );
}



export default Admin_dashboard