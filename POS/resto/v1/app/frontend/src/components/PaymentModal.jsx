import React, { useState } from 'react';

const PaymentModal = ({ isOpen, onClose, totalAmount, onPaymentConfirmed }) => {
  const [selectedMethod, setSelectedMethod] = useState(null);
  const [cashReceived, setCashReceived] = useState('');
  const [change, setChange] = useState(0);

  const handleCashInput = (e) => {
    const value = parseFloat(e.target.value);
    setCashReceived(e.target.value);
    if (!isNaN(value)) {
      setChange(value - totalAmount);
    } else {
      setChange(0);
    }
  };

  const handleConfirmCash = () => {
    if (parseFloat(cashReceived) < totalAmount) {
      alert("Insufficient cash.");
      return;
    }
    onPaymentConfirmed('cash', parseFloat(cashReceived), change);
    onClose();
  };

  const handleOtherPayment = (method) => {
    //onPaymentConfirmed(method);
    alert("Not available on DEMO");
    onClose();
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black/85 flex items-center justify-center z-50">
      <div className="bg-white p-6 rounded-lg shadow-lg w-full max-w-sm">
        {!selectedMethod && (
          <>
            <h2 className="text-lg mb-4 text-center">Select Payment Method</h2>
            <div className="space-y-2">
              <button onClick={() => setSelectedMethod('cash')} className="w-full py-2 bg-green-500 text-white rounded hover:bg-red-600">Cash</button>
              <button onClick={() => handleOtherPayment('gcash')} className="w-full py-2 bg-blue-600 text-white rounded hover:bg-purple-700">GCash</button>
              <button onClick={() => handleOtherPayment('card')} className="w-full py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">Card</button>
            </div>
            <button onClick={onClose} className="mt-4 w-full py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Cancel</button>
          </>
        )}

        {selectedMethod === 'cash' && (
          <>
            <h2 className="text-lg mb-4 text-center">Enter Cash Received</h2>
            <p className="mb-2 text-gray-700">Subtotal: ₱{totalAmount.toFixed(2)}</p>
            <input
              type="number"
              min={totalAmount}
              value={cashReceived}
              onChange={handleCashInput}
              placeholder="Cash amount"
              className="w-full p-2 mb-2 border rounded"
            />
            <p className="mb-4 text-sm text-green-600">
              Change: ₱{change > 0 ? change.toFixed(2) : '0.00'}
            </p>
            <div className="flex justify-between">
              <button onClick={() => setSelectedMethod(null)} className="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Back</button>
              <button onClick={handleConfirmCash} className="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">Confirm Payment</button>
            </div>
          </>
        )}
      </div>
    </div>
  );
};

export default PaymentModal;
