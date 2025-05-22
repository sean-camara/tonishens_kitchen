document.addEventListener('DOMContentLoaded', () => {

  // Show/hide Add Category Form
  document.getElementById('show-add-category-form').onclick = () => {
    const f = document.getElementById('add-category-form');
    f.hidden = !f.hidden;
    f.classList.add('animate__fadeInUp');
  };
  document.getElementById('add-category-cancel').onclick = () => {
    document.getElementById('add-category-form').hidden = true;
  };

  // Save new category
  document.getElementById('add-category-save').onclick = () => {
    const name = document.getElementById('new-category-name').value.trim();
    if (!name) return alert('Please enter a category name.');

    ajaxAction('add_category', { name }, () => {
      document.getElementById('add-category-form').hidden = true;
      document.getElementById('new-category-name').value = '';
      loadCategories(); // Refresh dropdowns
    });
  };

  // Back button goes to admin.php
  const backBtn = document.getElementById("back-btn");
  if (backBtn) {
    backBtn.addEventListener("click", () => {
      window.location.href = "admin.php";
    });
  }

  // Initial load
  loadCategories();
  loadInventory();

  // Show/hide Add Ingredient Form
  document.getElementById('show-add-form').onclick = () => {
    const f = document.getElementById('add-form');
    f.hidden = !f.hidden;
    f.classList.add('animate__fadeInUp');
  };
  document.getElementById('add-cancel').onclick = () => {
    document.getElementById('add-form').hidden = true;
  };

  // Save new ingredient (POST to PHP including quantity)
  document.getElementById('add-save').onclick = () => {
    const data = {
      name: document.getElementById('new-name').value,
      category_id: document.getElementById('new-category').value,
      unit: document.getElementById('new-unit').value,
      quantity: document.getElementById('new-quantity').value,
      reorder_level: document.getElementById('new-reorder').value,
      cost_per_unit: document.getElementById('new-cost').value,
    };
    ajaxAction('add_ingredient', data, () => {
      document.getElementById('add-form').hidden = true;
      loadInventory();
    });
  };

  // Filtering & searching
  document.getElementById('filter-category').onchange =
  document.getElementById('search-box').oninput = () => loadInventory();
});

function loadCategories() {
  fetch('admin-inventory-action.php?action=load_categories')
    .then(r => r.json())
    .then(json => {
      const sel = document.getElementById('filter-category');
      const sel2 = document.getElementById('new-category');
      sel.innerHTML = '<option value="">All Categories</option>';
      sel2.innerHTML = '';
      json.data.forEach(cat => {
        sel.innerHTML += `<option value="${cat.id}">${cat.name}</option>`;
        sel2.innerHTML += `<option value="${cat.id}">${cat.name}</option>`;
      });
    })
    .catch(err => console.error('Error loading categories:', err));
}

function loadInventory() {
  const cat = document.getElementById('filter-category').value;
  const q = document.getElementById('search-box').value;

  fetch(`admin-inventory-action.php?action=load_inventory&category=${cat}&q=${encodeURIComponent(q)}`)
    .then(response => response.json())
    .then(json => {
      if (!json.success) {
        console.error('Failed to load inventory:', json.message);
        return;
      }
      const tbody = document.querySelector('#inventory-table tbody');
      tbody.innerHTML = '';

      // If no data, show friendly message
      if (json.data.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="7" style="text-align:center; padding:1rem;">
              No ingredients found.
            </td>
          </tr>`;
        return;
      }

      json.data.forEach(item => {
        const tr = document.createElement('tr');

        // Highlight low-stock rows
        if (parseFloat(item.quantity) <= parseFloat(item.reorder_level)) {
          tr.classList.add('low-stock');
        }

        tr.innerHTML = `
          <td>${item.name}</td>
          <td>${item.category}</td>
          <td>${item.unit}</td>
          <td data-field="quantity">${item.quantity}</td>
          <td data-field="reorder_level">${item.reorder_level}</td>
          <td data-field="cost_per_unit">${item.cost_per_unit}</td>
          <td>
            <button class="blue-btn btn-edit">Edit</button>
            <button class="delete-btn btn-delete">Delete</button>
          </td>`;
        bindRowEvents(tr, item.id);
        tbody.appendChild(tr);
      });
    })
    .catch(err => {
      console.error('Error fetching inventory:', err);
    });
}

function bindRowEvents(tr, id) {
  // Delete button
  tr.querySelector('.btn-delete').onclick = () => {
    if (!confirm('Delete this ingredient?')) return;
    ajaxAction('delete_ingredient', { id }, () => loadInventory());
  };

  // Edit button (only reorder_level & cost_per_unit are editable)
  tr.querySelector('.btn-edit').onclick = () => {
    tr.classList.add('editing');
    tr.querySelectorAll('td[data-field]').forEach(td => {
      const field = td.getAttribute('data-field');
      if (field === 'quantity') {
        // Skip quantity (read-only)
        return;
      }
      const val = td.textContent;
      td.innerHTML = `<input type="text" value="${val}">`;
    });

    const btn = tr.querySelector('.btn-edit');
    btn.textContent = 'Save';
    btn.onclick = () => {
      const updated = { id };
      tr.querySelectorAll('td[data-field]').forEach(td => {
        const field = td.getAttribute('data-field');
        if (field === 'quantity') return;
        updated[field] = td.querySelector('input').value;
      });
      ajaxAction('update_ingredient_info', updated, () => loadInventory());
    };
  };
}

function ajaxAction(action, params, cb) {
  const body = new URLSearchParams({ action, ...params });
  fetch('admin-inventory-action.php', {
    method: 'POST',
    body
  })
  .then(r => r.json())
  .then(res => {
    if (!res.success) throw new Error(res.message);
    cb();
  })
  .catch(err => console.error(err));
}
