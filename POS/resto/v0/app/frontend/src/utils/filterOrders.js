export function filterOrders(orders, filters) {
  const now = new Date();

  return orders.filter((order) => {



    const matchesSearch =
      filters.search === "" ||
      order.id.toString().includes(filters.search) ||
      (order.table_id && order.table_id.toString().includes(filters.search)) ||
      (order.customer_name &&
        order.customer_name.toLowerCase().includes(filters.search.toLowerCase()));

    const matchesOrderType =
      filters.orderType === "all" || order.type === filters.orderType;

    const matchesStatus =
      filters.status === "all" ||
      order.status?.toLowerCase().replace(/\s/g, "_") === filters.status;

    const matchesPayment =
      filters.paymentMethod === "all" || order.payment_method === filters.paymentMethod;

  const createdDate = new Date(order.created_at);
const now = new Date();

const matchesDate = (() => {
  if (filters.dateRange === "today") {
    return createdDate.toDateString() === now.toDateString();
  } 
  else if (filters.dateRange === "yesterday") {
    const yesterday = new Date(now);
    yesterday.setDate(now.getDate() - 1);
    return createdDate.toDateString() === yesterday.toDateString();
  } 
  else if (filters.dateRange === "this_week") {
    const startOfWeek = new Date(now);
    startOfWeek.setDate(now.getDate() - now.getDay()); // Sunday of current week
    startOfWeek.setHours(0, 0, 0, 0);

    const endOfWeek = new Date(now);
    endOfWeek.setHours(23, 59, 59, 999);

    return createdDate >= startOfWeek && createdDate <= endOfWeek;
  } 
  else if (filters.dateRange === "custom") {
    if (!filters.customFrom || !filters.customTo) return true; // skip filter if incomplete

    const from = new Date(filters.customFrom);
    from.setHours(0, 0, 0, 0);

    const to = new Date(filters.customTo);
    to.setHours(23, 59, 59, 999);

    return createdDate >= from && createdDate <= to;
  }

  return true; // default no filtering by date
})();




    return (
      matchesSearch &&
      matchesOrderType &&
      matchesStatus &&
      matchesPayment &&
      matchesDate
    );
  });
}
