import { useState } from 'react'
import { Routes, Route, useLocation } from 'react-router-dom'
import Admin_NavBar from './components/Admin_Navbar'
import Admin_dashboard from './pages/Admin_dashboard'
import Admin_orders from './pages/Admin_orders'
import Admin_Menu from './pages/Admin_menu'
import Admin_users from './pages/Admin_users'
import Admin_reports from './pages/Admin_reports'
import Login from './pages/Login';
import { AuthProvider } from './contexts/AuthContext';
import ProtectedRoute from './components/ProtectedRoute';
import './css/tailwind.css'
import WaiterUi from './pages/WaiterUi';

function App() {
  const location = useLocation();
  const isLoginPage = location.pathname === '/';
  const isWaiterUi = location.pathname === '/WaiterUi';

  // Show Admin_NavBar on admin pages only
  const showAdminNavBar = [
    '/Admin_dashboard',
    '/Admin_orders',
    '/Admin_menu',
    '/Admin_users',
    '/Admin_reports'
  ].includes(location.pathname);

  return (
    <AuthProvider>
      <div className="h-screen bg-white">
        {showAdminNavBar && <Admin_NavBar />}
        <div className={`flex-1 ${showAdminNavBar ? 'md:pl-64' : ''}`}>
          <main className='main-content '> 
            <Routes>
              <Route path="/" element={<Login />} />
              <Route path="/unauthorized" element={<div>Unauthorized Access</div>} />
              
              <Route path='/Admin_dashboard' element={
                <ProtectedRoute allowedRoles={['admin']}>
                  <Admin_dashboard/>
                </ProtectedRoute>
              }/>
              <Route path='/Admin_orders' element={
                <ProtectedRoute allowedRoles={['admin']}>
                  <Admin_orders/>
                </ProtectedRoute>
              }/>
              <Route path='/Admin_menu' element={
                <ProtectedRoute allowedRoles={['admin']}>
                  <Admin_Menu/>
                </ProtectedRoute>
              }/>
              <Route path='/Admin_users' element={
                <ProtectedRoute allowedRoles={['admin']}>
                  <Admin_users/>
                </ProtectedRoute>
              }/>
              <Route path='/Admin_reports' element={
                <ProtectedRoute allowedRoles={['admin']}>
                  <Admin_reports/>
                </ProtectedRoute>
              }/>
              <Route path='/WaiterUi' element={
                <ProtectedRoute allowedRoles={['waiter']}>
                  <WaiterUi/>
                </ProtectedRoute>
              }/>
            </Routes>
          </main>
        </div>
      </div>
    </AuthProvider>
  )
}

export default App
