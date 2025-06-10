import { useState, useContext, useEffect } from 'react';
import { useRequireAuth } from '../contexts/AuthContext';
import { AuthContext } from '../contexts/AuthContext';
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/react';
import {
    ChevronDownIcon,
    Cog6ToothIcon,
    QuestionMarkCircleIcon,
    ArrowLeftCircleIcon,
    ArrowPathIcon,
    ViewColumnsIcon,
    XMarkIcon,
  } from "@heroicons/react/24/outline";
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';

dayjs.extend(relativeTime);
dayjs.extend(utc);
dayjs.extend(timezone)
dayjs.tz.setDefault("Asia/Taipei")



const ChefUi = () => {
  const [isLoading, setIsLoading] = useState(false);
  const [loadingDoneItemId, setLoadingDoneItemId] = useState(null); //Done Queue loading
  const [currentTime, setCurrentTime] = useState(new Date());
  const [lastRefreshTime, setLastRefreshTime] = useState('');
  const [errorMessage, setErrorMessage] = useState('');
  const [queue, setQueue] = useState([]);
  const user = useRequireAuth();
  const { logout } = useContext(AuthContext);
  const handleSignOut = (e) => {
    e.preventDefault();
    logout();
  };

   useEffect(() => {
  fetchOrderlist();
    const timer = setInterval(() => {
    setCurrentTime(new Date());
  }, 1000);
  return () => clearInterval(timer);
  }, []);

  const fetchOrderlist = async () => {
    setIsLoading(true);
  try {
    const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/order-items-chef`);
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
    setIsLoading(false);
    
  }
  };

  const handleMarkDone = async (Id, menuItemId) => {

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
      fetchOrderlist();
    } else {
      setErrorMessage(data.message);
    }
  } catch (err) {
    console.error('Error updating table:', err);
    setErrorMessage('Failed to update table');
  } finally {

  }
  };

  return (
    <>
    <div className="bg-[#2B4F4B] flex items-center">
      {/* Mobile Cart Toggle */}
      <div className="flex items-center justify-between p-4 gap-4 text-white">
                  <button onClick={fetchOrderlist}>
                    <ArrowPathIcon className="h-6 w-6" />
                  </button>
                   <div className="italic text-white text-xs">
                    last refresh {lastRefreshTime || 'N/A'}
                  </div>
                </div>
      <Menu as="div" className="w-full flex justify-end">
        <MenuButton as="div" className="gap-2 p-2 hover:bg-gray-200 ">
                  <span className="flex items-center gap-2 text-white">
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
    </div>
    <div className="p-4">
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
        Kitchen Queue / Todo
      </div>

      { isLoading ? (
        <p className="text-gray-500 text-center">Loading Menu Queue...</p>
      ) : queue.length === 0 ? (
        <p className="text-gray-500 text-center">No items in queue. 🎉</p>
      ) : (
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          {queue.map((item) => {
          const isLoadingDone = loadingDoneItemId === item.order_id-item.menu_item_id;

          return (
            <div 
            key={item.id}
            className={`rounded-lg shadow p-4 space-y-2 border ${
                item.status === 'queued'
                  ? 'bg-gray-50 text-gray-300 border-gray-300'
                  : 'bg-white text-black border'
              }`}>
                
              <div className={`grid grid-cols-2 text-xs${
                    item.status === 'queued'
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
                const uniqueId =item.order_id-item.menu_item_id;
                setLoadingDoneItemId(uniqueId);
                handleMarkDone(item.id, item.menu_item_id).finally(() => {
                  setLoadingDoneItemId(null);
                });
              }}

                className="mt-2 bg-green-600 hover:bg-green-700 text-white text-sm py-2 px-3 rounded"
>
                {isLoadingDone ? 'Removing...' : 'Done Preparing'}
              </button>
            </div>
          )
        }
          )}
        </div>
      )}
    </div>
  </>);
};

export default ChefUi;
