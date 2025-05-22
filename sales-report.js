// sales-report.js

// Grab DOM elements
const timeFilter      = document.getElementById('timeFilter');
const reportTableBody = document.getElementById('reportTableBody');
const exportBtn       = document.getElementById('exportBtn');
const ctx             = document.getElementById('salesChart').getContext('2d');
const grandTotalEl    = document.getElementById('grandTotal');

let salesChart = null; // will hold the Chart.js instance

// 1) Fetch data from PHP endpoint
async function fetchSalesData(filter) {
  try {
    const response = await fetch('fetch-sales-data.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `filter=${encodeURIComponent(filter)}`
    });
    if (!response.ok) throw new Error('Network response was not ok');
    return await response.json();
  } catch (err) {
    console.error('Error fetching sales data:', err);
    return null;
  }
}

// 2) Update the bar chart
function updateChart(salesData) {
  const labels      = salesData.map(item => item.dish_name);
  const amounts     = salesData.map(item => item.total_sales_amount);
  const background  = 'rgba(255, 119, 80, 0.7)';
  const borderColor = '#FF7750';

  const chartData = {
    labels: labels,
    datasets: [{
      label: 'Sales Amount (₱)',
      data: amounts,
      backgroundColor: background,
      borderColor: borderColor,
      borderWidth: 1,
      borderRadius: 5,
      hoverBackgroundColor: 'rgba(255, 119, 80, 0.9)',
      hoverBorderColor: '#cc633f',
    }]
  };

  const config = {
    type: 'bar',
    data: chartData,
    options: {
      responsive: true,
      animation: {
        duration: 800,
        easing: 'easeInOutQuart',
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: value => `₱${value}`,
          }
        }
      },
      plugins: {
        legend: {
          labels: {
            color: '#2D2D2D',
            font: { family: 'Poppins', size: 14, weight: '600' }
          }
        },
        tooltip: {
          callbacks: {
            label: context => `₱${context.parsed.y.toFixed(2)}`
          }
        }
      }
    }
  };

  if (salesChart) {
    salesChart.data    = chartData;
    salesChart.options = config.options;
    salesChart.update();
  } else {
    salesChart = new Chart(ctx, config);
  }
}

// 3) Populate the orders table
function updateTable(orderList) {
  reportTableBody.innerHTML = ''; // clear previous rows

  if (!orderList || orderList.length === 0) {
    reportTableBody.innerHTML = `
      <tr>
        <td colspan="6" style="text-align:center; padding:1rem;">
          No orders found for this period.
        </td>
      </tr>`;
    return;
  }

  orderList.forEach(order => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${order.order_id}</td>
      <td>${order.customer_name}</td>
      <td>${new Date(order.order_time).toLocaleString()}</td>
      <td>₱${order.total_amount.toFixed(2)}</td>
      <td>${order.payment_method}</td>
      <td>${order.dishes_ordered}</td>
    `;
    reportTableBody.appendChild(tr);
  });
}

// 4) Calculate and display grand total of all orders
function updateGrandTotal(orderList) {
  if (!orderList || orderList.length === 0) {
    grandTotalEl.textContent = '0.00';
    return;
  }
  // Sum up total_amount from each order (ensure it's treated as a number)
  const sum = orderList.reduce((acc, order) => {
    // If total_amount comes as a string, convert to float:
    const value = parseFloat(order.total_amount) || 0;
    return acc + value;
  }, 0);
  // Display with two decimal places
  grandTotalEl.textContent = sum.toFixed(2);
}

// 5) Export table to CSV
function exportTableToCSV(filename = 'sales_report.csv') {
  const rows = Array.from(reportTableBody.querySelectorAll('tr'));
  if (rows.length === 0) {
    alert('No data to export.');
    return;
  }

  // CSV headers
  const headers = ['Order ID', 'Customer', 'Date/Time', 'Total', 'Payment Method', 'Dishes Ordered'];
  const csvRows = [headers.join(',')];

  rows.forEach(row => {
    // Skip “no orders found” row
    if (row.querySelector('td[colspan]')) return;

    const cols = Array.from(row.querySelectorAll('td')).map(td => {
      let text = td.textContent.trim();
      if (text.includes(',') || text.includes('"')) {
        text = `"${text.replace(/"/g, '""')}"`;
      }
      return text;
    });
    csvRows.push(cols.join(','));
  });

  const blob = new Blob([csvRows.join('\n')], { type: 'text/csv' });
  const url  = URL.createObjectURL(blob);
  const a    = document.createElement('a');
  a.href     = url;
  a.download = filename;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}

// 6) Main loader function
async function loadAndDisplayData() {
  const filter = timeFilter.value;
  const data   = await fetchSalesData(filter);
  if (data) {
    // data.salesData → used for chart
    // data.orderList → used for table + grand total
    updateChart(data.salesData);
    updateTable(data.orderList);
    updateGrandTotal(data.orderList); // NEW: show the grand total
  }
}

// 7) Setup listeners on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
  // a) When filter changes, reload
  timeFilter.addEventListener('change', loadAndDisplayData);

  // b) When “Export to CSV” is clicked
  exportBtn.addEventListener('click', () => exportTableToCSV());

  // c) Initial load (default “today”)
  loadAndDisplayData();
});
