import { MagnifyingGlassIcon } from "@heroicons/react/24/outline";

import React, { useState } from "react";

const Admin_orders = () => {
  const [filters, setFilters] = useState({
    dateRange: "today",
    search: "",
    orderType: "all",
    status: "all",
    paymentMethod: "all",
  });

  return (
    <>
    <div className="p-4 space-y-4">
      <h2 className="text-l">Filter Orders</h2>

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
          className="border rounded px-3 py-2 "
          value={filters.dateRange}
          onChange={(e) => setFilters({ ...filters, dateRange: e.target.value })}
          title="Filter orders by date created"
        >
          <option value="today">Today</option>
          <option value="yesterday">Yesterday</option>
          <option value="this_week">This Week</option>
          <option value="custom">Custom Range</option>
        </select>

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

    <div className="p-4 space-y-4 mt-5">
        <h2 className="text-l">List</h2>
            <table className="table-auto min-w-full w-full text-xs md:text-sm text-left">
                <thead className="bg-gray-200">
                    <tr className="">
                    <th className="p-2 hidden md:table-cell">Order ID</th>
                    <th className="p-2">Table/Type</th>
                    <th className="p-2 hidden md:table-cell">Items</th>
                    <th className="p-2">Total</th>
                    <th className="p-2 hidden md:table-cell">Discount</th>
                    <th className="p-2">Status</th>
                    <th className="p-2">Created @</th>
                    </tr>
                </thead>
                <tbody>
                <tr className="border-b-1 border-gray-200 hover:bg-gray-200">
                    <td className="p-2 hidden md:table-cell">#10231</td>
                    <td className="p-2 ">Table3/Dine-in</td>
                    <td className="p-2 hidden md:table-cell">5 items</td>
                    <td className="p-2">₱1,230.00</td>
                    <td className="p-2 text-red-500 hidden md:table-cell">₱50.00</td>
                    <td className="p-2">
                    <span
                        className="inline-block px-2 py-1 text-xs font-semibold rounded bg-green-100">
                        paid
                    </span>
                    </td>
                    <td className="p-2">Apr 20, 3:45 PM</td>
                </tr>

                </tbody>
            </table>
    </div>
    </>
  );
};

export default Admin_orders