import { useState, useContext, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { AuthContext } from '../contexts/AuthContext';
import.meta.env.VITE_API_BASE_URL




function Login() {
  const [isLoading, setIsLoading] = useState(false);
  const [id, setId] = useState('');
  const [password, setPassword] = useState('');
  const [rememberMe, setRememberMe] = useState(false);
  const navigate = useNavigate();
  const { login } = useContext(AuthContext);

  useEffect(() => {
    const savedId = localStorage.getItem('rememberedId');
    if (savedId) {
      setId(savedId);
      setRememberMe(true);
    }
  }, []);

  const handleLogin = async (e) => {
    e.preventDefault();
    setIsLoading(true);
    try {
      const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, password }),
      });

      const data = await response.json();
      if (data.success) {
        login(data.user);
        alert(data.message);

        // Save ID in localStorage if "Remember Me" is checked
        if (rememberMe) {
          localStorage.setItem('rememberedId', id);
        } else {
          localStorage.removeItem('rememberedId');
        }

        // Role-based navigation
        switch (data.user.role) {
          case 'admin':
            navigate('/Admin_dashboard');
            break;
          case 'cashier':
            navigate('/CashierUi');
            break;
          case 'waiter':
            navigate('/WaiterUi');
            break;
          case 'chef':
            navigate('/ChefUi');
            break;
          default:
            alert('Invalid role or insufficient permissions');
        }
      } else {
        alert(data.message);
      }
    } catch (error) {
      console.error('Error:', error);
      alert('An error occurred. Server not connected.');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="flex items-center justify-center h-screen">
      <form onSubmit={handleLogin} className="bg-white p-6 rounded w-full sm:w-80">
        <img 
                      className=" object-cover h-40 w-40 mx-auto"
                      src="src/assets/R-Click Profile White BG.png"
                      alt="R-Click Solutions Logo"
                    />
        <p className="text-l mb-4 text-center">R-Click Solutions - Resto POS</p>
        
        <div className="mb-4">
          <label className="block text-sm font-medium mb-1" htmlFor="id">ID</label>
          <input
            type="text"
            id="id"
            value={id}
            onChange={(e) => setId(e.target.value)}
            className="w-full px-3 py-2 border rounded"
            required
          />
        </div>
        
        <div className="mb-4">
          <label className="block text-sm font-medium mb-1" htmlFor="password">Password</label>
          <input
            type="password"
            id="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            className="w-full px-3 py-2 border rounded"
            required
          />
        </div>

        <div className="mb-4 flex items-center">
          <input
            type="checkbox"
            id="rememberMe"
            checked={rememberMe}
            onChange={(e) => setRememberMe(e.target.checked)}
            className="mr-2"
          />
          <label htmlFor="rememberMe" className="text-xs">Remember Me</label>
        </div>

        <button
          type="submit"
          className="w-full bg-gray-500 text-white py-2 rounded hover:bg-[#2B4F4B] text-sm"
          disabled={isLoading}
        >
          {isLoading ? 'Loading...' : 'Login'}
        </button>
      </form>
    </div>
  );
}

export default Login;
