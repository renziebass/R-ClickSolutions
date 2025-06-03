import React, { useState, useRef, useEffect } from 'react';
import.meta.env.VITE_API_BASE_URL

const userRoles = [
    'Admin',
    'Cashier',
    'Waiter',
    'Chef',
  ];

  export default function Admin_users(){
  const [search, setSearch] = useState('');
  const [roleFilter, setRoleFilter] = useState('all');
  const [statusFilter, setStatusFilter] = useState('all');
  const [onlineFilter, setOnlineFilter] = useState('all');

  
  const [isLoading, setIsLoading] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');
  
  const [selectedItem, setSelectedItem] = useState(null)
  const [isOpen, setIsOpen] = useState(false)
  const [Users, setUsers] = useState([]);
  const [newUser, setNewUser] = useState({
    name: '',
    email: '',
    password_hash: '',
    role: '',
  });
  const [query, setQuery] = useState('')
  const dropdownRef = useRef()
  const [isFormOpen, setIsFormOpen] = useState(false);
  
  const filteredRoles = userRoles.filter((user) =>
    user.toLowerCase().includes(query.toLowerCase())
  );

  const [editingItem, setEditingItem] = useState(null);
  const [formData, setFormData] = useState([]);
    
  const handleEditClick = (item) => {
  setEditingItem(item);
  setFormData(item);
  };

  useEffect(() => {
    setNewUser(prev => ({
      ...prev,
      role: query,
    }));
    fetchUsers();
    const handler = (e) => {
      if (!dropdownRef.current?.contains(e.target)) {
        setIsOpen(false)
      }
    }
    document.addEventListener('mousedown', handler)
    return () => document.removeEventListener('mousedown', handler)
  }, [query])

  const handleSelect = (user) => {
    setSelectedItem(user)
    setQuery(user)
    setIsOpen(false)
  }
  
  const fetchUsers = async () => {
    setIsLoading(true);
    try {
      const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/users`);
      const data = await response.json();
      if (data.success) {
        setUsers(data.data);
        setErrorMessage('');
      } else {
        setErrorMessage(data.message);
      }
    } catch (error) {
      setErrorMessage('Error fetching users');
      console.error('Error:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleAddUser = async () => {
    // Add validation with better checks
    if (!newUser.name.trim() || 
        !newUser.email.trim() || 
        !newUser.password_hash.trim() || 
        !newUser.role.trim()) {
      setErrorMessage('Please fill in all required fields');
      return;
    }
  
    setIsLoading(true);
    try {
      const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/users`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          ...newUser
        })
      });
  
      const data = await response.json();
      
      if (data.success) {
        setNewUser({
          name: '',
          email: '',
          password_hash: '',
          role: ''
        });
        setErrorMessage('');
        fetchUsers();
        setIsFormOpen(false);
      } else {
        setErrorMessage(data.message || 'Failed to add user1');
      }
    } catch (error) {
      setErrorMessage('Failed to add user2');
      console.error('Error:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleUpdateUser = async () => {
    // Add validation with better checks
    if ( 
        !formData.name.trim() || 
        !formData.email.trim() || 
        !formData.password_hash.trim() || 
        !formData.role.trim()) {
      setErrorMessage('Please fill in all required fields');
      return;
    }
  
    setIsLoading(true);
    try {
      const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/update-users`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          ...formData
        })
      });
  
      const data = await response.json();
      
      if (data.success) {
        setFormData('');
        setErrorMessage('');
        fetchUsers();
        setEditingItem(null);
      } else {
        setErrorMessage(data.message || 'Failed to update user');
      }
    } catch (error) {
      setErrorMessage('Failed to update user1');
      console.error('Error:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const disableUser = async (userId) => {
  
    try {
      const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/disable-user`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          user_id: userId
        }),
      });
  
      const data = await response.json();
      if (data.success) {
        // Update local cart state
        fetchUsers();
      } else {
        setErrorMessage(data.message);
      }
    } catch (err) {
      console.error('Error updating user:', err);
      setErrorMessage('Failed to user');
    }
};

  const filteredUsers = Users.filter((user) => {
  const matchSearch = user.name.toLowerCase().includes(search.toLowerCase());
  const matchRole = roleFilter === 'all' || user.role === roleFilter;
  const matchStatus = statusFilter === 'all' || 
  (statusFilter === '1' && user.is_active === 1) ||
  (statusFilter === '0' && user.is_active === 0);
  const matchOnline = onlineFilter === 'all' ||
  (onlineFilter === '1' && user.is_online === 1)||
  (onlineFilter === '0' && user.is_online === 0);
  return matchSearch && matchRole && matchStatus && matchOnline;
});



return (
  <div className="p-4 space-y-4">
    {/* Filters */}
    {!isFormOpen && !editingItem && (
        <button
          className="p-2 bg-gray-200 rounded hover:bg-[#2B4F4B] text-sm hover:text-white"
          onClick={() => setIsFormOpen(true)}
        >
          Add user
        </button>
      )}
      {isFormOpen && !editingItem && (
      <div className="relative bg-gray-200 p-4 rounded shadow space-y-4 text-xs md:text-sm">
      <button
        className="absolute top-2 right-2 text-gray-600 hover:text-black"
        onClick={() => setIsFormOpen(false)}
                title="Close"
      >
        ✕
      </button>

        <h3 className="">Add New User</h3>
        <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div className="w-auto relative col-span-2" ref={dropdownRef}>
            <input
              type="text"
              value={query.toLowerCase()}
              onChange={(e) => {
                setNewUser({...newUser, role: e.target.value})
                setQuery(e.target.value)
                setIsOpen(true)
                
              }}
              onFocus={() => setIsOpen(true)}
              placeholder="Select or type..."
              className="px-3 py-2 border rounded w-full"
            />
            {isOpen && filteredRoles.length > 0 && (
              <ul className="absolute z-10 mt-1 w-full bg-white border rounded overflow-auto">
                {filteredRoles.map((user, index) => (
                  <li
                    key={index}
                    onClick={() => handleSelect(user)}
                    className="px-3 py-2 cursor-pointer hover:bg-gray-200 hover:text-black rounded"
                  >
                    {user}
                  </li>
                ))}
              </ul>
            )}
          </div>
          <input
            type="text"
            placeholder="Email"
            className="border px-3 py-2 rounded"
            value={newUser.email}
            onChange={(e) => setNewUser({...newUser, email: e.target.value})}
          />
           <input
            type="text"
            placeholder="Full name"
            className="border px-3 py-2 rounded"
            value={newUser.name}
            onChange={(e) => setNewUser({...newUser, name: e.target.value})}
          />
          <input
            type="text"
            placeholder="Password"
            className="border px-3 py-2 rounded"
            value={newUser.password_hash}
            onChange={(e) => setNewUser({...newUser, password_hash: e.target.value})}
          />
         
          
        </div>
        <div className="flex justify-end-safe">
        <button
            onClick={handleAddUser}
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
      {editingItem && (
      <div className="relative bg-gray-200 p-4 rounded shadow space-y-4 text-xs md:text-sm">
      <button
        className="absolute top-2 right-2 text-gray-600 hover:text-black"
        onClick={() => setEditingItem(null)}
                title="Close"
      >
        ✕
      </button>

        <h3 className="">Edit User</h3>
        <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div className="w-auto relative col-span-2" ref={dropdownRef}>
            <input
              type="text"
              value={formData.role.toLowerCase()}
              onChange={(e) => {
                setFormData({...formData, role: e.target.value})
                setQuery(e.target.value)
                setIsOpen(true)
                
              }}
              onFocus={() => setIsOpen(true)}
              placeholder="Select or type..."
              className="px-3 py-2 border rounded w-full"
            />
            {isOpen && filteredRoles.length > 0 && (
              <ul className="absolute z-10 mt-1 w-full bg-white border rounded overflow-auto">
                {filteredRoles.map((user, index) => (
                  <li
                    key={index}
                    onClick={() => handleSelect(user)}
                    className="px-3 py-2 cursor-pointer hover:bg-gray-200 hover:text-black rounded"
                  >
                    {user}
                  </li>
                ))}
              </ul>
            )}
          </div>
          <input
            type="text"
            placeholder="Email"
            className="border px-3 py-2 rounded"
            value={formData.email}
            onChange={(e) => setFormData({...formData, email: e.target.value})}
          />
           <input
            type="text"
            placeholder="Full name"
            className="border px-3 py-2 rounded"
            value={formData.name}
            onChange={(e) => setFormData({...formData, name: e.target.value})}
          />
          <input
            type="text"
            placeholder="Password"
            className="border px-3 py-2 rounded"
            value={formData.password_hash}
            onChange={(e) => setFormData({...formData, password_hash: e.target.value})}
          />


        </div>
        <div className="flex justify-end-safe">
        <button
            onClick={handleUpdateUser}
            disabled={isLoading}
            className={`${
              isLoading ? 'bg-gray-400' : 'bg-[#4FA3A5]'
            } text-white px-4 py-2 rounded`}
          >
            {isLoading ? 'Updating...' : 'Update'}
          </button>
          {errorMessage && (
            <p className="text-red-500 text-sm mt-2">{errorMessage}</p>
          )}
        </div>
        </div>

      )}
      {!isFormOpen && !editingItem && (
    <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-1 text-sm">
      <input
        type="text"
        placeholder="Search by name or email..."
        className="border px-3 py-2 rounded-md col-span-2"
        value={search}
        onChange={(e) => setSearch(e.target.value)}
      />

      <select
        className="border px-3 py-2 rounded-md"
        value={roleFilter}
        onChange={(e) => setRoleFilter(e.target.value)}
      >
        <option value="all">All Roles</option>
        {userRoles.map((user, index) => (
          <option key={index} value={user.toLowerCase()}>
            {user}
          </option>
        ))}
      </select>

      <select
        className="border px-3 py-2 rounded-md"
        value={statusFilter}
        onChange={(e) => setStatusFilter(e.target.value)}
      >
        <option value="all">All Active</option>
        <option value="1">Active</option>
        <option value="0">Inactive</option>
      </select>

      <select
        className="border px-3 py-2 rounded-md"
        value={onlineFilter}
        onChange={(e) => setOnlineFilter(e.target.value)}
      >
        <option value="all">All Online</option>
        <option value="1">Online</option>
        <option value="0">Offline</option>
      </select>

    </div>
    )}
    {!isFormOpen && !editingItem && (
    <table className="min-w-full w-full text-xs md:text-sm text-left">
      <thead>
        <tr className="bg-gray-200 text-xs md:text-sm">
          <th className="p-2">Name</th>
          <th className="p-2 hidden sm:table-cell">Email</th>
          <th className="p-2">Role</th>
          <th className="p-2">Created At</th>
          <th className="p-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        {filteredUsers.map((user) => (
          <tr key={user.id} className="border-b hover:bg-gray-100">
            <td className="p-2">{user.name}</td>
            <td className="p-2 hidden sm:table-cell">{user.email}</td>
            <td className="p-2">{user.role}</td>
            <td className="p-2">{new Date(user.created_at).toLocaleDateString()}</td>
            <td className="p-2 flex gap-2 print:hidden">
              <button className={`${user.is_active === 1 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'} px-2 py-1 rounded text-xs hover:bg-blue-500 hover:text-white`} 
                    onClick={() => disableUser(user.id)}>{user.is_active === 1 ? 'Disable' : 'Enable'}
                  </button>
              <button
                onClick={() => handleEditClick(user)}
                className="px-2 py-1 rounded text-xs bg-gray-200 hover:bg-blue-600"
              >
                Edit
              </button>
            </td>
          </tr>
        ))}
      </tbody>
    </table>
    )}
  </div>
);
}