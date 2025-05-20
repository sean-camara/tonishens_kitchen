// orders.js

document.addEventListener('DOMContentLoaded', () => {
  const statusFilter = document.getElementById('statusFilter');
  const dateFrom     = document.getElementById('dateFrom');
  const dateTo       = document.getElementById('dateTo');
  const applyFilters = document.getElementById('applyFilters');

  const selectAll    = document.getElementById('selectAll');
  const bulkStatus   = document.getElementById('bulkStatus');
  const applyBulk    = document.getElementById('applyBulk');

  const ordersTbody  = document.getElementById('ordersTbody');

  // 1) Fetch and render orders with optional filters
  async function fetchAndRenderOrders() {
    const data = new URLSearchParams();
    data.append('status', statusFilter.value);
    data.append('date_from', dateFrom.value);
    data.append('date_to', dateTo.value);

    try {
      const res = await fetch('fetch-orders.php', {
        method: 'POST',
        body: data
      });
      const json = await res.json();
      renderOrdersTable(json.orders);
    } catch (err) {
      console.error('Error fetching orders:', err);
    }
  }

  // 2) Render the orders into the table body
  function renderOrdersTable(orders) {
    ordersTbody.innerHTML = '';

    if (!orders.length) {
      ordersTbody.innerHTML = `
        <tr>
          <td colspan="7" style="text-align:center; padding:1rem;">
            No orders found for these filters.
          </td>
        </tr>`;
      return;
    }

    orders.forEach(order => {
      const tr = document.createElement('tr');

      // Checkbox cell
      const cbTd = document.createElement('td');
      const cb = document.createElement('input');
      cb.type = 'checkbox';
      cb.classList.add('row-checkbox');
      cb.value = order.order_id;
      cbTd.appendChild(cb);

      // Order ID
      const idTd = document.createElement('td');
      idTd.textContent = order.order_id;

      // Customer
      const custTd = document.createElement('td');
      custTd.textContent = order.customer;

      // Date/Time
      const dtTd = document.createElement('td');
      dtTd.textContent = new Date(order.order_time).toLocaleString();

      // Total
      const totTd = document.createElement('td');
      totTd.textContent = `₱${order.total_amount.toFixed(2)}`;

      // Status (badge + inline dropdown on hover)
      const statusTd = document.createElement('td');
      const badge = document.createElement('span');
      badge.className = `status-badge status-${order.status.replace(/\s/g, '\\ ')}`;
      badge.textContent = order.status;
      badge.dataset.orderId = order.order_id;
      badge.addEventListener('click', () => openStatusDropdown(order.order_id, order.status, badge));
      statusTd.appendChild(badge);

      // Actions (View, Cancel, Print)
      const actionsTd = document.createElement('td');

      // a) View button
      const viewBtn = document.createElement('button');
      viewBtn.className = 'action-btn view';
      viewBtn.title = 'View Details';
      viewBtn.innerHTML = '<i class="fa-solid fa-eye"></i>';
      viewBtn.addEventListener('click', () => {
        window.open(`order-detail.php?order_id=${order.order_id}`, '_blank');
      });

      // b) Cancel button (only if not already Canceled/Completed)
      const cancelBtn = document.createElement('button');
      cancelBtn.className = 'action-btn cancel';
      cancelBtn.title = 'Cancel Order';
      cancelBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
      if (order.status === 'Canceled' || order.status === 'Completed') {
        cancelBtn.disabled = true;
        cancelBtn.style.opacity = 0.5;
      } else {
        cancelBtn.addEventListener('click', () => cancelOrder(order.order_id));
      }

      // c) Print button
      const printBtn = document.createElement('button');
      printBtn.className = 'action-btn print';
      printBtn.title = 'Print Receipt';
      printBtn.innerHTML = '<i class="fa-solid fa-print"></i>';
      printBtn.addEventListener('click', () => {
        window.open(`order-detail.php?order_id=${order.order_id}&print=1`, '_blank');
      });

      actionsTd.append(viewBtn, cancelBtn, printBtn);

      // Assemble row
      tr.append(cbTd, idTd, custTd, dtTd, totTd, statusTd, actionsTd);
      ordersTbody.appendChild(tr);
    });
  }

  // 3) Open inline status dropdown (replacing the badge temporarily)
  function openStatusDropdown(order_id, currentStatus, badgeEl) {
    // Build the dropdown
    const select = document.createElement('select');
    ['Pending','Preparing','on-the-way','Completed','Canceled'].forEach(optionVal => {
      const opt = document.createElement('option');
      opt.value = optionVal;
      opt.textContent = optionVal;
      if (optionVal === currentStatus) opt.selected = true;
      select.appendChild(opt);
    });
    select.style.fontSize = '0.9rem';
    select.style.padding = '0.2rem';

    // Replace badge with select
    badgeEl.replaceWith(select);
    select.focus();

    // When focus is lost or value changed, update status
    select.addEventListener('change', async () => {
      const newStatus = select.value;
      const data = new URLSearchParams();
      data.append('order_id', order_id);
      data.append('new_status', newStatus);

      // Call update-order-status.php
      try {
        const res = await fetch('update-order-status.php', {
          method: 'POST',
          body: data
        });
        const json = await res.json();
        if (json.success) {
          // Rebuild the badge with new status
          const newBadge = document.createElement('span');
          newBadge.className = `status-badge status-${newStatus.replace(/\s/g, '\\ ')}`;
          newBadge.textContent = newStatus;
          newBadge.dataset.orderId = order_id;
          newBadge.addEventListener('click', () => openStatusDropdown(order_id, newStatus, newBadge));
          select.replaceWith(newBadge);
        } else {
          alert('Failed to update status: ' + json.message);
          // Revert to original badge
          select.replaceWith(badgeEl);
        }
      } catch (err) {
        console.error('Error updating status:', err);
        select.replaceWith(badgeEl);
      }
    });

    // If user clicks outside, revert without changing
    select.addEventListener('blur', () => {
      badgeEl.replaceWith(badgeEl);
      select.remove();
    });
  }

  // 4) Cancel a single order
  async function cancelOrder(order_id) {
    if (!confirm('Are you sure you want to cancel order #' + order_id + '?')) return;
    const data = new URLSearchParams();
    data.append('order_id', order_id);

    try {
      const res = await fetch('cancel-order.php', {
        method: 'POST',
        body: data
      });
      const json = await res.json();
      if (json.success) {
        // Refresh the table
        fetchAndRenderOrders();
      } else {
        alert('Failed to cancel: ' + json.message);
      }
    } catch (err) {
      console.error('Error canceling order:', err);
    }
  }

  // 5) Bulk update selected orders
  applyBulk.addEventListener('click', async () => {
    const newStatus = bulkStatus.value;
    if (!newStatus) {
      alert('Please select a bulk status.');
      return;
    }

    // Gather all checked checkboxes
    const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
    const ids = Array.from(checkedBoxes).map(cb => cb.value);
    if (!ids.length) {
      alert('No orders selected.');
      return;
    }

    if (!confirm(`Change status of ${ids.length} order(s) to "${newStatus}"?`)) return;

    const data = new URLSearchParams();
    data.append('new_status', newStatus);
    ids.forEach(id => data.append('order_ids[]', id));

    try {
      const res = await fetch('bulk-update-orders.php', {
        method: 'POST',
        body: data
      });
      const json = await res.json();
      if (json.success) {
        fetchAndRenderOrders();
      } else {
        alert('Bulk update failed: ' + json.message);
      }
    } catch (err) {
      console.error('Error bulk updating:', err);
    }
  });

  // 6) “Select All” checkbox logic
  selectAll.addEventListener('change', () => {
    const allBoxes = document.querySelectorAll('.row-checkbox');
    allBoxes.forEach(cb => { cb.checked = selectAll.checked; });
  });

  // 7) Apply filters when “Apply Filters” clicked
  applyFilters.addEventListener('click', () => {
    fetchAndRenderOrders();
  });

  // Initial load
  fetchAndRenderOrders();
});
