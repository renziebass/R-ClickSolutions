import React from 'react';
import dayjs from 'dayjs';

const KitchenQueueCards = ({ data }) => {
  // Group items by order_id
  const grouped = {};

  data.forEach(item => {
    if (!grouped[item.order_id]) {
      grouped[item.order_id] = {
        order_id: item.order_id,
        order_type: item.order_type,
        updated_at: item.updated_at,
        identifier: item.identifier,
        items: []
      };
    }

    grouped[item.order_id].items.push({
      quantity: item.quantity,
      name: item.name,
      status: item.status,
      note: item.note
    });
  });

  const cardData = Object.values(grouped);

  return (
    <>
      {cardData.map((order) => (
        <div
          key={order.order_id}
          className="rounded-lg shadow p-4 space-y-2 border"
        >
          <div className="mb-3 grid grid-cols-2">
            <p >{order.identifier}</p>
            <p className="text-end">
              {dayjs.utc(order.updated_at).tz('Asia/Manila').fromNow()}
            </p>
          </div>
          

          <ul className="divide-y divide-gray-100">
            {order.items.map((item, index) => (
              <li key={index} className="py-2 flex justify-between items-center">
                <div>
                  <span className="font-semibold">{item.quantity} x</span>{' '}
                  {item.name}
                  {item.note && (
                    <p className="text-s text-red-500 italic" hidden={!item.note}>
                      Note: {item.note}
                    </p>
                  )}
                </div>
                <span
                  className={
                          item.status === 'queued' ? 'text-black-500' :
                          item.status === 'preparing' ? 'text-yellow-500' :
                          item.status === 'ready' ? 'text-blue-500' :
                          item.status === 'served' ? 'text-green-500' :
                          ''
                        }
                >
                  {item.status}
                </span>
              </li>
            ))}
          </ul>
        </div>
      ))}
    </>
  );
};

export default KitchenQueueCards;
