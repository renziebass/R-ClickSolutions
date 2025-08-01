import React, { useEffect, useState } from 'react';
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
import {
    PrinterIcon
  } from "@heroicons/react/24/outline";
import.meta.env.VITE_API_BASE_URL

const COLORS = [
 '#2B4F4B', // Deep teal (brand base)
  '#F28F3B', // Warm orange (friendly, contrasting)
  '#4F706E', // Slate green (neutral balance)
  '#E4572E', // Vivid coral red (energy & distinction)
  '#9AAEAC', // Soft green-gray (calming mid-tone)
  '#76B041', // Fresh green (stands out on dark UI)
  '#C1CDCB', // Pale gray-blue (neutral relief)
  '#3F88C5', // Soft blue (cool balance)
  '#B63E6A', // Deep rose (rich contrast)
  '#FFD23F'  // Bright yellow-gold (highlight color)
];



  


function Admin_reports(){
  
  const [isLoading, setIsLoading] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');

  const [MinMaxDates, setMinMaxDates] = useState([]);
  const [selectedDates, setSelectedDates] = useState([]);
  const [ReportDetails1, setReportDetails1] = useState([]);
  const [ReportDetails2, setReportDetails2] = useState([]);
  const [ReportDetails3, setReportDetails3] = useState([]);
  const [ReportDetails4, setReportDetails4] = useState([]);
  const [menuItemsSummary1, setMenuItemsSummary1] = useState([]);
  const [menuItemsSummary2, setMenuItemsSummary2] = useState([]);

  const BarChartData = [
    {
      name: 'Dine-in',
      orders: ReportDetails1.dine_in,
    },
    {
      name: 'Take-out',
      orders: ReportDetails1.take_out,
    },
    {
      name: 'Delivery',
      orders: ReportDetails1.delivery,
    },
  ];


const RADIAN = Math.PI / 180;

// Custom label renderer with basic responsiveness
const renderResponsiveLabel = ({
  cx, cy, midAngle, outerRadius, name, total_quantity, chartWidth
}) => {
  const radius = outerRadius + 20;
  const x = cx + radius * Math.cos(-midAngle * RADIAN);
  const y = cy + radius * Math.sin(-midAngle * RADIAN);

  const fullLabel = `${name}`;
  const words = fullLabel.split(' ');
  const mid = Math.ceil(words.length / 2);
  const firstLine = words.slice(0, mid).join(' ');
  const secondLine = words.slice(mid).join(' ');

  // Make font size responsive to chart width
  const fontSize = Math.max(10, chartWidth / 50); // e.g. 10–14px

  return (
    <text
      x={x}
      y={y}
      fill="#333"
      fontSize={`${fontSize}px`}
      textAnchor={x > cx ? 'start' : 'end'}
      dominantBaseline="central"
    >
      <tspan x={x} dy="-0.6em">{firstLine}</tspan>
      <tspan x={x} dy="1.2em">{secondLine}</tspan>
    </text>
  );
};

// Wrapper for passing chart size to label renderer
const ResponsivePieChart = ({ data, colorMap }) => {
  return (
    <ResponsiveContainer width="100%" height={400}>
      <PieChart>
        <Pie
          data={data}
          dataKey="total_quantity"
          outerRadius="60%"
          innerRadius="28%"
          label={({ ...args }) => renderResponsiveLabel({ ...args, chartWidth: 400 })}
          labelLine={true}
        >
          {data.map((entry, index) => (
            <Cell key={`cell-${index}`} fill={colorMap[entry.name]} />
          ))}
        </Pie>
        <Tooltip />
      </PieChart>
    </ResponsiveContainer>
  );
};




  const handlePrint = () => {
  alert('Printing is disabled for this demo. Contact Mr. Renzie Operario Bassig @09772332632 for more information.');
  /*const printContents = printRef.current.innerHTML;
  const originalContents = document.body.innerHTML;

  document.body.innerHTML = printContents;
  window.print();
  document.body.innerHTML = originalContents;
  window.location.reload();
  */
  };

  const handleDemo = () => {
  alert('This feature is not Available for demo. Contact Mr. Renzie Operario Bassig @09772332632 for more information.');
  /*const printContents = printRef.current.innerHTML;
  const originalContents = document.body.innerHTML;

  document.body.innerHTML = printContents;
  window.print();
  document.body.innerHTML = originalContents;
  window.location.reload();
  */
  };


  const fetchDates = async () => {
  try {
    const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/fetch-dates`);
    const data = await response.json();

    if (data.success) {
     const fixDate = (rawDate) => {
      const d = new Date(rawDate);
      const offset = d.getTimezoneOffset() * 60000;
      return new Date(d.getTime() - offset).toISOString().split('T')[0];
    };

    const newDates = {
      date1: fixDate(data.data[0].date1),
      date2: fixDate(data.data[0].date2),
    };

      setMinMaxDates(newDates);
      setErrorMessage('');
    } else {
      setErrorMessage(data.message);
    }
  } catch (error) {
    setErrorMessage('Error fetching dates');
    console.error('Error:', error);
  }
  };

  const fetchReportDetails1 = async () => {
  setIsLoading(true);
  try {
    const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/fetch-report-details1?date1=${selectedDates.date1}&date2=${selectedDates.date2}`);
    const data = await response.json();

    if (data.success) {
      const details = data.data[0];
      setReportDetails1(details);
      fetchReportDetails2();
      setErrorMessage('');
    } else {
      setErrorMessage(data.message);
    }
  } catch (error) {
    setErrorMessage('Error fetching report details 1');
    console.error('Error:', error);
  } finally {

  }
  };

  const fetchReportDetails2 = async () => {
 
  try {
    const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/fetch-report-details2?date1=${selectedDates.date1}&date2=${selectedDates.date2}`);
    const data = await response.json();

    if (data.success) {
      setReportDetails2(data.data);
      setErrorMessage('');
      fetchReportDetails3();
    } else {
      setErrorMessage(data.message);
    }
  } catch (error) {
    setErrorMessage('Error fetching report details 2');
    console.error('Error:', error);
  } finally {

  }
  };

  const fetchReportDetails3 = async () => {
  try {
    const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/fetch-report-details3?date1=${selectedDates.date1}&date2=${selectedDates.date2}`);

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    if (data.success && Array.isArray(data.data)) {
      const fixDate = (rawDate) => {
        const d = new Date(rawDate);
        if (isNaN(d.getTime())) {
          console.warn('Invalid date:', rawDate);
          return null;
        }
        const offset = d.getTimezoneOffset() * 60000;
        return new Date(d.getTime() - offset).toISOString().split('T')[0];
      };

      const salesData = data.data.map(item => ({
        date: fixDate(item.date),
        total: item.total,
      })).filter(item => item.date !== null); // Filter out any bad dates

      setReportDetails3(salesData);
      fetchReportDetails4();
      setErrorMessage('');
    } else {
      setErrorMessage(data.message || 'Unexpected data format');
    }
  } catch (error) {
    setErrorMessage('Error fetching report details 3');
    console.error('Error:', error);
  }
  };

  const fetchReportDetails4 = async () => {
 
  try {
    const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/fetch-report-details4?date1=${selectedDates.date1}&date2=${selectedDates.date2}`);
    const data = await response.json();

    if (data.success) {
      setReportDetails4(data.data);
      fetchMenuItemsSummary1();
      setErrorMessage('');
    } else {
      setErrorMessage(data.message);
    }
  } catch (error) {
    setErrorMessage('Error fetching report details 2');
    console.error('Error:', error);
  } finally {

  }
  };

  const fetchMenuItemsSummary1 = async () => {
  
  try {
    const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/fetch-menu-items-summary1?date1=${selectedDates.date1}&date2=${selectedDates.date2}`);
    const data = await response.json();
    if (data.success) {
      setMenuItemsSummary1(data.data);
      fetchMenuItemsSummary2();
      setErrorMessage('');
    } else {
      setErrorMessage(data.message);
    }
  } catch (error) {
    setErrorMessage('Error fetching Data');
    console.error('Error:', error);
  } finally {
 
  }
  };

  const fetchMenuItemsSummary2 = async () => {
  
  try {
    const response = await fetch(`${import.meta.env.VITE_APP_API_BASE_URL}/api/fetch-menu-items-summary2?date1=${selectedDates.date1}&date2=${selectedDates.date2}`);
    const data = await response.json();
    if (data.success) {
      setMenuItemsSummary2(data.data);
      setErrorMessage('');
    } else {
      setErrorMessage(data.message);
    }
  } catch (error) {
    setErrorMessage('Error fetching Data');
    console.error('Error:', error);
  } finally {
     setIsLoading(false);
  }
  };

  useEffect(() => {
    fetchDates();

  }, [])

  const sortedData = [...ReportDetails2].sort((a, b) => b.total_quantity - a.total_quantity);
  const colorMap = {};
  sortedData.forEach((entry, index) => {
    colorMap[entry.name] = COLORS[index % COLORS.length];
  });

  return (
    <div className="p-4 space-y-6">
      <div className='grid grid-cols-2'>
        <h1 className="text-l">Admin Reports</h1>
        <div className="flex justify-end gap-1">
          <button className='p-2 bg-gray-200 rounded hover:bg-[#2B4F4B] hover:text-white text-sm'
          onClick={handleDemo}>
          Monthly Report
        </button>
        <button className='p-2 bg-gray-200 rounded hover:bg-[#2B4F4B] hover:text-white text-sm'
          onClick={handleDemo}>
          Yearly Report
        </button>
          <button className="p-2 bg-gray-200 rounded hover:bg-[#2B4F4B] hover:text-white" onClick={handlePrint}><PrinterIcon className="size-5" /></button>
        </div>
      </div>
      {/* Filters */}
      <div className="gap-1 text-sm flex items-center">
       
          <p>From:</p>
          <input type="date"
          className="border p-2 rounded-md " 
          value={selectedDates.date1}
          min={MinMaxDates.date1}
          onChange={(e) => setSelectedDates({ ...selectedDates, date1: e.target.value })}
          />
          <p className=''>To:</p>
          <input type="date"
          className="border p-2 rounded-md"
          value={selectedDates.date2}
          max={MinMaxDates.date2}
          onChange={(e) => setSelectedDates({ ...selectedDates, date2: e.target.value })} 
          />
          <button className='p-2 bg-gray-200 rounded hover:bg-[#2B4F4B] hover:text-white text-sm'
          onClick={fetchReportDetails1}>
          Fetch Report
          </button>
  
      </div>

      {/* KPI Cards */}
      { isLoading ? 
      (
        <div className="h-100 flex items-center justify-center">
          <p className="text-gray-600 animate-pulse">Creating report...</p>
        </div>
      ) : ReportDetails4.length === 0 ? (
        <div className="h-100 flex items-center justify-center ">
          <p className="text-red-500 italic text-center">
            Set both <span className="underline">From Date</span> and <span className="underline">To Date</span> to generate a report.
          </p>
        </div>
      ) :
      (
        <>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-2">

        <div className="p-2">
          <div>
            <p className="text-xs text-gray-500">Gross Profit</p>
            <p className="text-l">₱ {(ReportDetails1.gross_profit || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
          </div>
          <div className="grid grid-cols-2">
            <div>
              <p className="text-xs text-gray-500">Revenue</p>
              <p className="text-l">₱ {(ReportDetails1.revenue || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
            </div>
            <div>
              <p className="text-xs text-gray-500">COGS</p>
              <p className="text-l">₱ {(ReportDetails1.cogs || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
            </div>
          </div>
        </div>

        <div className="p-2">
          <div>
            <p className="text-xs text-gray-500">Monthly Avg</p>
            <p className="text-l">₱ {(ReportDetails1.monthly_avg || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
          </div>
          <div>
            <p className="text-xs text-gray-500">Daily Avg</p>
            <p className="text-l">₱ {(ReportDetails1.daily_avg || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
          </div>
        </div>

        <div className="p-2">
          <div>
            <p className="text-xs text-gray-500">Customers Monthly Avg</p>
            <p className="text-l">{(ReportDetails1.customers_monthly_avg || 0)}</p>
          </div>
          <div>
            <p className="text-xs text-gray-500">Customers Daily Avg</p>
            <p className="text-l">{(ReportDetails1.customers_daily_avg || 0)}</p>
          </div>
        </div>


      </div>

      {/* Charts */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-3">

        <div className="p-2">
          <p className="text-l mb-2">Top 10 Menu</p>
          <ResponsivePieChart data={ReportDetails2} colorMap={colorMap} />

        </div>

        <div className="p-2">
          <p className="text-l mb-2">{ReportDetails1.total_customers} Orders</p>
          <ResponsiveContainer width="100%" height={350}>
           <BarChart width={150} height={40} data={BarChartData}
           label
           barSize={80}>
            <XAxis dataKey="name" />
            <Tooltip />
            <Bar dataKey="orders" fill="#2B4F4B">

            </Bar>
           </BarChart>
          </ResponsiveContainer>
        </div>

      </div>

      <div className="">

        <div className="p-2 ">
          <p className="text-l mb-2">Sales Chart</p>
          <ResponsiveContainer width="100%" height={350}>
            <LineChart data={ReportDetails3}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="date" />
              <YAxis />
              <Tooltip
                  formatter={(value) =>
                    (Number(value)).toLocaleString('en-US', {
                      minimumFractionDigits: 2,
                      maximumFractionDigits: 2,
                    })
                  }
                />
              
              <Line type="monotone" dataKey="total" stroke="#2B4F4B" strokeWidth={2} />
            </LineChart>
          </ResponsiveContainer>
        </div>

      </div>

      <div className="">

        <div className="p-4 bg-gray-50 rounded-md">
          <p className="mb-4">Sales Data</p>
          <div className='min-h-auto max-h-96 overflow-y-auto'>
               <table className='w-full text-sm text-center'>
            <thead>
              <tr className='text-gray-600 text-sm sticky top-0 bg-gray-50'>
                <th>Date</th>
                <th>Dine-in</th>
                <th>Take-out</th>
                <th>Delivery</th>
                <th>Sales</th>
                <th>Best seller</th>
              </tr>
            </thead>
            <tbody>
             {ReportDetails4.map((data) => (
                <tr className="text-gray-700 hover:bg-gray-100">
                  <td className="p-2">{new Date(data.payment_date).toLocaleDateString()}</td>
                  <td className="p-2">
                    ₱{
                      (Number(data.dine_in_sales)).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                      })
                      }
                    </td>
                  <td className="p-2">
                    ₱{
                    (Number(data.take_out_sales)).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                      })
                    }
                    </td>
                  <td className="p-2">
                    ₱{
                    (Number(data.delivery_sales)).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                      })
                    }
                  </td>
                  <td className="p-2">
                    ₱{
                    (Number(data.total_sales)).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                      })
                    }
                  </td>
                  <td className="p-2">{data.best_seller}</td>
                </tr>
              ))}
            </tbody>
          </table>
          </div>
        </div>

      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
    
        <div className="p-4  bg-gray-50 rounded-md">
          <p className="mb-4">Menu Items Order Rangking</p>
          <div className='h-96 overflow-y-auto'>
               <table className='w-full text-sm text-center'>
                <thead>
                  <tr className='text-gray-600 text-sm sticky top-0 bg-gray-50'>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th className='hidden sm:table-cell'>Capital Total</th>
                    <th>Sales</th>
                    <th>Profit</th>
                  </tr>
                </thead>
                <tbody>
                  {menuItemsSummary1.map((data) => (
                    <tr className="text-gray-700 hover:bg-gray-100">
                      <td className="p-2">{data.menu_item}</td>
                      <td className="p-2">{data.total_quantity}</td>
                      <td className="p-2 hidden sm:table-cell">
                        ₱{
                        (Number(data.capital_total)).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                        })
                        }</td>
                      <td className="p-2">
                        ₱{
                        (Number(data.total_revenue)).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                        })
                        }</td>
                      <td className="p-2">
                        ₱{
                        (Number(data.gross_profit)).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                        })
                        }</td>
                    </tr>
                  ))}
                </tbody>
              </table>
          </div>
        </div>

        <div className="p-4  bg-gray-50 rounded-md">
          <p className="mb-4">Beverages Order Rangking</p>
          <div className='h-96 overflow-y-auto'>
               <table className='w-full text-sm text-center'>
                <thead>
                  <tr className='text-gray-600 text-sm sticky top-0 bg-gray-50'>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th className='hidden sm:table-cell'>Capital Total</th>
                    <th>Sales</th>
                    <th>Profit</th>
                  </tr>
                </thead>
                <tbody>
                  {menuItemsSummary2.map((data) => (
                    <tr className="text-gray-700 hover:bg-gray-100">
                      <td className="p-2">{data.menu_item}</td>
                      <td className="p-2">{data.total_quantity}</td>
                      <td className="p-2 hidden sm:table-cell">
                        ₱{
                        (Number(data.capital_total)).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                        })
                        }</td>
                      <td className="p-2">
                        ₱{
                        (Number(data.total_revenue)).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                        })
                        }</td>
                      <td className="p-2">
                        ₱{
                        (Number(data.gross_profit)).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                        })
                        }</td>
                    </tr>
                  ))}
                </tbody>
              </table>
          </div>
        </div>
 
      </div>
        
        </>
      )

      }


    </div>
  );
}

export default Admin_reports