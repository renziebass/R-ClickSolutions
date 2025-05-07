import React, { useState } from 'react';

const salesData = {
    todayTotal: 5000,
    topItem: 'Burger',
    ordersInProgress: 10,
    kitchenQueue: [
      { item: 'Burger', orderId: 101, timeLeft: '5 min' },
      { item: 'Pizza', orderId: 102, timeLeft: '8 min' },
      { item: 'Pasta', orderId: 103, timeLeft: '3 min' },
      { item: 'Steak', orderId: 104, timeLeft: '10 min' },
      { item: 'Salad', orderId: 105, timeLeft: '2 min' },
    ],
    drinksQueue: [
      { item: 'Iced Tea', orderId: 201, timeLeft: '4 min' },
      { item: 'Mojito', orderId: 202, timeLeft: '6 min' },
      { item: 'Lemonade', orderId: 203, timeLeft: '3 min' },
    ],
  };

function Admin_dashboard(){
    return (
        <div className="p-4 space-y-8">
          <h1 className="text-2xl font-semibold text-gray-800">Admin Dashboard</h1>
    
          {/* KPI Cards */}
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div className="bg-white shadow-lg rounded-lg p-6">
              <p className="text-gray-500">Total Sales Today</p>
              <p className="text-2xl font-bold text-green-600">₱{salesData.todayTotal}</p>
            </div>
    
            <div className="bg-white shadow-lg rounded-lg p-6">
              <p className="text-gray-500">Top Selling Item</p>
              <p className="text-2xl font-bold text-blue-600">{salesData.topItem}</p>
            </div>
    
            <div className="bg-white shadow-lg rounded-lg p-6">
              <p className="text-gray-500">Orders in Progress</p>
              <p className="text-2xl font-bold text-yellow-600">{salesData.ordersInProgress}</p>
            </div>
          </div>
    
          {/* Kitchen Queue List */}
          <div className="bg-white shadow-lg rounded-lg p-6">
            <h2 className="text-lg font-semibold mb-4">Kitchen Queue</h2>
            <ul className="space-y-4">
              {salesData.kitchenQueue.map((item, index) => (
                <li key={index} className="flex justify-between items-center">
                  <span className="text-gray-700">Order #{item.orderId}: {item.item}</span>
                  <span className="text-sm text-gray-500">{item.timeLeft} remaining</span>
                </li>
              ))}
            </ul>
          </div>
    
          {/* Drinks Queue List */}
          <div className="bg-white shadow-lg rounded-lg p-6">
            <h2 className="text-lg font-semibold mb-4">Drinks Queue</h2>
            <ul className="space-y-4">
              {salesData.drinksQueue.map((item, index) => (
                <li key={index} className="flex justify-between items-center">
                  <span className="text-gray-700">Order #{item.orderId}: {item.item}</span>
                  <span className="text-sm text-gray-500">{item.timeLeft} remaining</span>
                </li>
              ))}
            </ul>
          </div>
        </div>
      );
}

export default Admin_dashboard