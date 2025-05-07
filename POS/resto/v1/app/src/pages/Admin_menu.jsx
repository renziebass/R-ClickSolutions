// MenuItemsTab.jsx
import React, { useState, useRef, useEffect } from 'react';

export default function Admin_Menu() {
  const [view, setView] = useState('table'); // 'table' or 'card'
  const [search, setSearch] = useState('');
  const [category, setCategory] = useState('all');
  const [availability, setAvailability] = useState('all');

  const [isFormOpen1, setIsFormOpen1] = useState(false);
  const [isFormOpen2, setIsFormOpen2] = useState(false);

  const [query, setQuery] = useState('')
  const [isOpen, setIsOpen] = useState(false)
  const [selectedItem, setSelectedItem] = useState(null)
  const dropdownRef = useRef()


  useEffect(() => {
    const handler = (e) => {
      if (!dropdownRef.current?.contains(e.target)) {
        setIsOpen(false)
      }
    }
    document.addEventListener('mousedown', handler)
    return () => document.removeEventListener('mousedown', handler)
  }, [])

  const handleSelect = (item) => {
    setSelectedItem(item)
    setNewMenuItem({...newMenuItem, category: item})
    setQuery(item)
    setIsOpen(false)
  }
  // Add these state variables
const [menuItems, setMenuItems] = useState([]);
const [newMenuItem, setNewMenuItem] = useState({
  name: '',
  description: '',
  category: '',
  price: '',
  capital_price: ''
});
const [CategoryItems, setCategoryItems] = useState([]);
const [newMenuCategory, setNewMenuCategory] = useState({
  category: '',
  description: ''
});

const [isLoading, setIsLoading] = useState(false);
const [errorMessage, setErrorMessage] = useState('');

// Add this function to fetch menu items
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

// Add this function to fetch menu items
const fetchMenuItems = async () => {
  setIsLoading(true);
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
const filteredItems = menuItems.filter((item) => {
  const matchSearch = item.name.toLowerCase().includes(search.toLowerCase());
  const matchCategory = category === 'all' || item.category === category;
  const matchStatus = availability === 'all' || 
    (availability === '1' && item.is_available === 1) || 
    (availability === '0' && item.is_available === 0);
  return matchSearch && matchCategory && matchStatus;
});

useEffect(() => {
  fetchMenuItems();
  fetchCategory(); // Fetch categories on component mount
}, []); // Fetch menu items on component mount
// Replace the existing handleAddMenuItem function
const handleAddMenuItem = async () => {
  // Add validation with better checks
  if (!newMenuItem.name.trim() || 
      !newMenuItem.category.trim() || 
      !Number(newMenuItem.price) || 
      !Number(newMenuItem.capital_price)) {
    setErrorMessage('Please fill in all required fields');
    return;
  }

  // Validate price and capital price are positive numbers
  if (Number(newMenuItem.price) <= 0 || Number(newMenuItem.capital_price) <= 0) {
    setErrorMessage('Prices must be greater than 0');
    return;
  }

  setIsLoading(true);
  try {
    const response = await fetch('http://localhost:3306/api/menu-items', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        ...newMenuItem,
        price: Number(newMenuItem.price),
        capital_price: Number(newMenuItem.capital_price)
      })
    });

    const data = await response.json();
    
    if (data.success) {
      setNewMenuItem({
        name: '',
        description: '',
        category: newMenuItem.category,
        price: '',
        capital_price: ''
      });
      setErrorMessage('');
      fetchMenuItems();
      setIsFormOpen1(false);
    } else {
      setErrorMessage(data.message || 'Failed to add menu item');
    }
  } catch (error) {
    setErrorMessage('Failed to add menu item');
    console.error('Error:', error);
  } finally {
    setIsLoading(false);
  }
};

const handleAddCategoryItem = async () => {
  // Add validation with better checks
  if (!newMenuCategory.category.trim()) {
    setErrorMessage('Please fill in the category field');
    return;
  }

  setIsLoading(true);
  try {
    const response = await fetch('http://localhost:3306/api/menu-category', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        ...newMenuCategory
      })
    });

    const data = await response.json();
    
    if (data.success) {
      setNewMenuCategory({
        category: '',
        description: ''
      });
      setErrorMessage('');
      setIsFormOpen2(false);
    } else {
      setErrorMessage(data.message || 'Failed to add menu category');
    }
  } catch (error) {
    setErrorMessage('Failed to add category');
    console.error('Error:', error);
  } finally {
    setIsLoading(false);
  }
};

// Update useEffect to fetch both categories and menu items

  return (
    <div className="p-4 space-y-4">
      {!isFormOpen1 && !isFormOpen2 && (
        <>
          <button
            className="px-3 py-2 rounded-md col-span-2 text-sm bg-[#4FA3A5] mr-2"
            onClick={() => setIsFormOpen1(true)}
          >
            Add item
          </button>
          <button
            className="px-3 py-2 rounded-md col-span-2 text-sm bg-[#4FA3A5]"
            onClick={() => setIsFormOpen2(true)}
          >
            Add category
          </button>
        </>
      )}
      {isFormOpen1 && (
  <div className="relative bg-gray-200 p-4 rounded shadow space-y-4 text-xs md:text-sm">
    <button
      className="absolute top-2 right-2 text-gray-600 hover:text-black"
      onClick={() => setIsFormOpen1(false)}
      title="Close"
    >
      ✕
    </button>
    <h3 className="">Add New Menu Item</h3>
    <form
      onSubmit={(e) => {
        e.preventDefault();
        handleAddMenuItem();
      }}
    >
      <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div className="w-auto relative col-span-2" ref={dropdownRef}>
          <input
            type="text"
            value={query}
            onChange={(e) => {
              setQuery(e.target.value)
              setIsOpen(true)
            }}
            onFocus={() => setIsOpen(true)}
            placeholder="Select or type..."
            className="px-3 py-2 border rounded w-full"
          />
          {isOpen && CategoryItems.length > 0 && (
            <ul className="absolute z-10 mt-1 w-full bg-white border rounded overflow-auto">
              {CategoryItems.map((item, index) => (
                <li
                  key={index}
                  onClick={() => handleSelect(item.name.toUpperCase())}
                  className="px-3 py-2 cursor-pointer hover:bg-gray-200 hover:text-black rounded"
                >
                  {item.name}
                </li>
              ))}
            </ul>
          )}
        </div>
        <input
          type="text"
          placeholder="Menu Name"
          className="border px-3 py-2 rounded col-span-2"
          value={newMenuItem.name}
          onChange={(e) => setNewMenuItem({...newMenuItem, name: e.target.value.toUpperCase()})}
        />
        <input
          type="number"
          placeholder="Raw Price"
          className="border px-3 py-2 rounded"
          value={newMenuItem.capital_price}
          onChange={(e) => setNewMenuItem({...newMenuItem, capital_price: e.target.value})}
        />
        <input
          type="number"
          placeholder="Price"
          className="border px-3 py-2 rounded"
          value={newMenuItem.price}
          onChange={(e) => setNewMenuItem({...newMenuItem, price: e.target.value})}
        />
        <input
          type="text"
          placeholder="Description"
          className="border px-3 py-2 rounded col-span-2"
          value={newMenuItem.description}
          onChange={(e) => setNewMenuItem({...newMenuItem, description: e.target.value.toUpperCase()})}
        />
      </div>
      <div className="flex justify-end-safe">
        <button
          type="submit"
          disabled={isLoading}
          className={`${
            isLoading ? 'bg-gray-400' : 'bg-[#4FA3A5]'
          } text-white px-4 py-2 rounded`}
        >
          {isLoading ? 'Adding...' : 'Add'}
        </button>
        {errorMessage && (
          <p className="text-red-500 text-sm mt-2">{errorMessage}</p>
        )}
      </div>
    </form>
  </div>
)}
      {isFormOpen2 && (
      <div className="relative bg-gray-200 p-4 rounded shadow space-y-4 text-xs md:text-sm">
      <button
        className="absolute top-2 right-2 text-gray-600 hover:text-black"
        onClick={() => setIsFormOpen2(false)}
                title="Close"
      >
        ✕
      </button>
        <h3 className="">Add New Category</h3>
        <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
          <input
            type="text"
            placeholder="category"
            className="border px-3 py-2 rounded"
            value={newMenuCategory.category}
            onChange={(e) => setNewMenuCategory({...newMenuCategory, category: e.target.value.toUpperCase()})}
          />
          <input
            type="text"
            placeholder="description"
            className="border px-3 py-2 rounded"
            value={newMenuCategory.description}
            onChange={(e) => setNewMenuCategory({...newMenuCategory, description: e.target.value.toUpperCase()})}
          />
        </div>
        <div className="flex justify-end-safe">
        <button
            onClick={handleAddCategoryItem}
            disabled={isLoading}
            className={`${
              isLoading ? 'bg-gray-400' : 'bg-[#4FA3A5]'
            } text-white px-4 py-2 rounded`}
          >
            {isLoading ? 'Adding...' : 'Add'}
          </button>
          {errorMessage && (
            <p className="text-red-500 text-sm mt-2">{errorMessage}</p>
          )}
        </div>
      </div>
      )}
      {!isFormOpen1 && !isFormOpen2 && (
      <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-1 text-sm">
      <select
          className="border px-3 py-2 rounded-md col-span-2"
          value={category}
          onChange={(e) => setCategory(e.target.value)}
        >
          <option value="all">All Categories</option>
          {CategoryItems.map((item, index) => (
            <option key={index} value={item.name}>
              {item.name}
            </option>
          ))}
        </select>

        <input
          type="text"
          placeholder="Search by name..."
          className="border px-3 py-2 rounded-md w-full md:w-full col-span-2"
          value={search}
          onChange={(e) => setSearch(e.target.value)}
        />

        <select
          className="border px-3 py-2 rounded-md"
          value={availability}
          onChange={(e) => setAvailability(e.target.value)}
        >
          <option value="all">All Status</option>
          <option value="1">Available</option>
          <option value="0">Unavailable</option>
        </select>

        <select
          className="border px-3 py-2 rounded-md"
          value={view}
          onChange={(e) => setView(e.target.value)}
        >
          <option value="table">Table View</option>
          <option value="card">Card View</option>
        </select>

      </div>
      )}

      {!isFormOpen1 && !isFormOpen2 && view === 'table' ? (
        <table className="min-w-full w-full text-xs md:text-sm text-left">
          <thead>
            <tr className="bg-gray-200 text-xs md:text-sm">
              <th className="p-2">Name</th>
              <th className="p-2">Category</th>
              <th className="p-2">Price</th>
              <th className="p-2">Status</th>
              <th className="p-2 hidden md:table-cell">Actions</th>
            </tr>
          </thead>
          <tbody>
            {filteredItems.map((item) => (
              <tr className="border-b-1 border-gray-200 hover:bg-gray-200" key={item.id}>
                <td className="p-2">{item.name}</td>
                <td className="p-2">{item.category}</td>
                <td className="p-2">₱{item.price.toFixed(2)}</td>
                <td className="p-2">
                  <span className={`px-2 py-1 rounded text-xs ${
                    item.is_available === 1 
                      ? 'bg-green-100 text-green-700' 
                      : 'bg-red-100 text-red-700'
                  }`}>
                    {item.is_available === 1 ? 'Available' : 'Unavailable'}
                  </span>
                </td>
                <td className="p-2 hidden md:table-cell">
                  <button className="text-blue-500 hover:underline mr-2">Edit</button>
                  <button className="text-gray-500 hover:underline">Archive</button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      ) : (
        !isFormOpen1 && !isFormOpen2 && (
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mt-4">
            {filteredItems.map((item) => (
              <div
                key={item.id}
                className="border rounded-lg p-4 shadow hover:shadow-md transition"
              >
                <img
                  src=""
                  alt={item.name}
                  className="w-full h-32 object-cover rounded mb-2"
                />
                <h3 className="font-semibold text-lg">{item.name}</h3>
                <p className="text-sm text-gray-500">{item.category}</p>
                <p className="text-sm font-medium mt-1">₱{item.price.toFixed(2)}</p>
                <p className={`text-xs mt-1 ${item.status === 1 ? 'text-green-600' : 'text-red-600'}`}>{item.status}</p>
                <div className="flex gap-2 mt-3">
                  <button className="text-blue-500 hover:underline text-sm">Edit</button>
                  <button className="text-gray-500 hover:underline text-sm">Archive</button>
                </div>
              </div>
            ))}
          </div>
        )
      )}
    </div>
  );
}
