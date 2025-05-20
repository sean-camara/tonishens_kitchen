document.addEventListener("DOMContentLoaded", () => {
  const DELIVERY_FEE = 50;
  const TAX_RATE = 0.12;

  // Elements
  const subtotalEl      = document.getElementById("subtotal");
  const salesTaxEl      = document.getElementById("tax");
  const deliveryFeeEl   = document.getElementById("delivery");
  const grandTotalEl    = document.getElementById("grand-total");

  const subtotalInput   = document.getElementById("subtotal-input");
  const taxInput        = document.getElementById("tax-input");
  const deliveryInput   = document.getElementById("delivery-fee-input");
  const grandTotalInput = document.getElementById("grand-total-input");

  const selectedItemsContainer = document.getElementById("selected-items-inputs");
  const selectAllCheckbox      = document.getElementById("select-all");

  // Recalculate Order Summary
  function calculateTotals() {
    let subtotal = 0;
    document
      .querySelectorAll("tbody tr.cart-row")
      .forEach(row => {
        const cb = row.querySelector(".custom-checkbox");
        if (cb && cb.checked) {
          const price    = parseFloat(row.dataset.price);
          const quantity = parseInt(row.dataset.qty);
          if (!isNaN(price) && !isNaN(quantity)) {
            subtotal += price * quantity;
          }
        }
      });

    const salesTax  = subtotal * TAX_RATE;
    const grandTotal = subtotal + salesTax + DELIVERY_FEE;

    subtotalEl.textContent    = `₱${subtotal.toFixed(2)}`;
    salesTaxEl.textContent    = `₱${salesTax.toFixed(2)}`;
    deliveryFeeEl.textContent = `₱${DELIVERY_FEE.toFixed(2)}`;
    grandTotalEl.innerHTML    = `<strong>₱${grandTotal.toFixed(2)}</strong>`;

    subtotalInput.value   = subtotal.toFixed(2);
    taxInput.value        = salesTax.toFixed(2);
    deliveryInput.value   = DELIVERY_FEE.toFixed(2);
    grandTotalInput.value = grandTotal.toFixed(2);

    // sync select-all
    if (selectAllCheckbox) {
      const all = Array.from(document.querySelectorAll("tbody .custom-checkbox"));
      selectAllCheckbox.checked = all.every(cb => cb.checked);
    }
  }

  // AJAX: update quantity
  function updateQuantity(id, action) {
    fetch("update_cart.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `id=${encodeURIComponent(id)}&action=${encodeURIComponent(action)}`
    })
    .then(r => r.json())
    .then(data => {
      if (!data.success) {
        alert("Failed to update quantity.");
        return;
      }
      const row = document.querySelector(`tr.cart-row[data-id="${id}"]`);
      if (!row) return;
      if (data.quantity === 0) {
        row.remove();
      } else {
        row.dataset.qty = data.quantity;
        const spanQty = row.querySelector(".qty-value");
        spanQty.textContent = data.quantity;
        const price     = parseFloat(row.dataset.price);
        const totalCell = row.querySelector("td:nth-child(6)");
        totalCell.textContent = `₱${(price * data.quantity).toFixed(2)}`;
      }
      calculateTotals();
    })
    .catch(() => alert("Error updating quantity."));
  }

  // AJAX: remove single item
  function removeItem(id) {
    fetch(`remove-from-cart.php?dish_id=${encodeURIComponent(id)}`, {
      method: "GET"
    })
    .then(r => r.json())
    .then(data => {
      if (!data.success) {
        alert("Failed to remove item.");
        return;
      }
      const row = document.querySelector(`tr.cart-row[data-id="${id}"]`);
      if (row) row.remove();
      calculateTotals();
    })
    .catch(() => alert("Error removing item."));
  }

  // hook quantity buttons
  document.querySelectorAll("button.qty-btn.increase").forEach(btn => {
    btn.addEventListener("click", e => {
      e.preventDefault();
      updateQuantity(btn.dataset.id, "increase");
    });
  });
  document.querySelectorAll("button.qty-btn.decrease").forEach(btn => {
    btn.addEventListener("click", e => {
      e.preventDefault();
      updateQuantity(btn.dataset.id, "decrease");
    });
  });

  // hook remove-icon links
  document.querySelectorAll("a.remove-item").forEach(link => {
    link.addEventListener("click", e => {
      e.preventDefault();
      if (!confirm("Remove this item?")) return;
      removeItem(link.dataset.id);
    });
  });

  // hook individual checkboxes
  document.querySelectorAll("tbody .custom-checkbox").forEach(cb => {
    cb.addEventListener("change", calculateTotals);
  });

  // hook select-all
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener("change", () => {
      const checked = selectAllCheckbox.checked;
      document.querySelectorAll("tbody .custom-checkbox").forEach(cb => {
        cb.checked = checked;
      });
      calculateTotals();
    });
  }

  // on checkout submit
  const checkoutForm = document.getElementById("checkout-form");
  if (checkoutForm) {
    checkoutForm.addEventListener("submit", e => {
      // block if nothing selected
      const any = Array.from(document.querySelectorAll("tbody .custom-checkbox"))
                       .some(cb => cb.checked);
      if (!any) {
        e.preventDefault();
        alert("Please select at least one dish before proceeding to checkout.");
        return;
      }

      // inject selected items
      selectedItemsContainer.innerHTML = "";
      document.querySelectorAll("tbody tr.cart-row").forEach(row => {
        const cb = row.querySelector(".custom-checkbox");
        if (!cb.checked) return;
        const id       = row.dataset.id;
        const name     = row.querySelector("td:nth-child(3)").textContent.trim();
        const price    = parseFloat(row.dataset.price);
        const quantity = parseInt(row.dataset.qty);
        selectedItemsContainer.innerHTML +=
          `<input type="hidden" name="items[${id}][name]" value="${name}">` +
          `<input type="hidden" name="items[${id}][price]" value="${price}">` +
          `<input type="hidden" name="items[${id}][quantity]" value="${quantity}">`;
      });

      calculateTotals();
    });
  }

  // initial
  calculateTotals();
});
