import React, { useState } from 'react';
import {
    LineChart,
    Line,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    Legend,
    ResponsiveContainer,
    BarChart,
    Bar,
    PieChart,
    Pie,
    Cell
  } from 'recharts';

  const salesData = [
    { date: 'Apr 16', total: 3500 },
    { date: 'Apr 17', total: 4200 },
    { date: 'Apr 18', total: 3900 },
    { date: 'Apr 19', total: 5100 },
    { date: 'Apr 20', total: 4700 },
    { date: 'Apr 21', total: 5890 },
  ];
  
  const orderTypeData = [
    { name: 'Dine-in', value: 140 },
    { name: 'Take-out', value: 70 },
    { name: 'Delivery', value: 35 },
  ];
  
  const employeePerformanceData = [
    { employee: 'Maria', ordersTaken: 120 },
    { employee: 'Juan', ordersTaken: 90 },
    { employee: 'Lia', ordersTaken: 75 },
  ];
  
  const topSellingMenuData = [
    { menuItem: 'Burger', sales: 120 },
    { menuItem: 'Pizza', sales: 95 },
    { menuItem: 'Pasta', sales: 85 },
  ];
  
  const COLORS = ['#34d399', '#60a5fa', '#fbbf24'];

function Admin_reports(){
    const [reportType, setReportType] = useState('sales');

  return (
    <div className="p-4 space-y-6">
      {/* Filters */}
      <div className="flex flex-col md:flex-row gap-4 justify-between items-center">
        <input type="date" className="border px-3 py-2 rounded-md" />
        <input type="date" className="border px-3 py-2 rounded-md" />
        <select
          className="border px-3 py-2 rounded-md"
          value={reportType}
          onChange={(e) => setReportType(e.target.value)}
        >
          <option value="sales">Sales</option>
          <option value="orders">Orders</option>
          <option value="discounts">Discounts</option>
          <option value="employeePerformance">Employee Performance</option>
        </select>
        <button className="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
          Export PDF
        </button>
      </div>

      {/* KPI Cards */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div className="bg-white shadow rounded p-4">
          <p className="text-sm text-gray-500">Gross Sales</p>
          <p className="text-xl font-bold text-green-600">₱47,890.00</p>
        </div>
        <div className="bg-white shadow rounded p-4">
          <p className="text-sm text-gray-500">Monthly Average Sales</p>
          <p className="text-xl font-bold text-blue-600">₱4,000.00</p>
        </div>
        <div className="bg-white shadow rounded p-4">
          <p className="text-sm text-gray-500">Daily Average Sales</p>
          <p className="text-xl font-bold text-purple-600">₱150.00</p>
        </div>
        <div className="bg-white shadow rounded p-4">
          <p className="text-sm text-gray-500">Top Selling Menu</p>
          <p className="text-xl font-bold text-yellow-600">Burger</p>
        </div>
      </div>

      {/* Charts */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-white shadow rounded p-4">
          <h3 className="text-lg font-semibold mb-2">Sales Over Time</h3>
          <ResponsiveContainer width="100%" height={250}>
            <LineChart data={salesData}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="date" />
              <YAxis />
              <Tooltip />
              <Legend />
              <Line type="monotone" dataKey="total" stroke="#4f46e5" strokeWidth={2} />
            </LineChart>
          </ResponsiveContainer>
        </div>

        <div className="bg-white shadow rounded p-4">
          <h3 className="text-lg font-semibold mb-2">Orders by Type</h3>
          <ResponsiveContainer width="100%" height={250}>
            <BarChart data={orderTypeData}>
              <XAxis dataKey="name" />
              <YAxis />
              <Tooltip />
              <Bar dataKey="value" fill="#3b82f6" />
            </BarChart>
          </ResponsiveContainer>
        </div>

        <div className="bg-white shadow rounded p-4">
          <h3 className="text-lg font-semibold mb-2">Employee Performance</h3>
          <ResponsiveContainer width="100%" height={250}>
            <BarChart data={employeePerformanceData}>
              <XAxis dataKey="employee" />
              <YAxis />
              <Tooltip />
              <Bar dataKey="ordersTaken" fill="#38bdf8" />
            </BarChart>
          </ResponsiveContainer>
        </div>

        <div className="bg-white shadow rounded p-4 col-span-1 lg:col-span-2">
          <h3 className="text-lg font-semibold mb-2">Top Selling Menu</h3>
          <ResponsiveContainer width="100%" height={250}>
            <BarChart data={topSellingMenuData}>
              <XAxis dataKey="menuItem" />
              <YAxis />
              <Tooltip />
              <Bar dataKey="sales" fill="#60a5fa" />
            </BarChart>
          </ResponsiveContainer>
        </div>
      </div>
    </div>
  );
}

export default Admin_reports