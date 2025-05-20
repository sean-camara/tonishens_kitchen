// home-menu.js
document.addEventListener('DOMContentLoaded', function () {
  const cartCount = document.getElementById('cart-count');

  // refresh cart count on load (optional)
  fetch('get-cart-count.php')
    .then(r => r.json())
    .then(data => {
      if (data.count > 0) {
        cartCount.textContent = data.count;
        cartCount.style.display = 'inline-block';
      }
    })
    .catch(() => {});

  // ADD TO CART
  document.querySelectorAll('.cart-btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault(); e.stopPropagation();
      const id = this.closest('.card').dataset.id;

      // animate
      this.textContent = '✔ Added!';
      this.style.background = '#28a745';
      this.style.color = '#fff';
      setTimeout(() => {
        this.textContent = 'ADD TO CART';
        this.style.background = '';
        this.style.color = '';
      }, 1200);

      fetch('add-to-cart.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'dish_id=' + encodeURIComponent(id)
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          cartCount.textContent = data.count;
          cartCount.style.display = 'inline-block';
          cartCount.classList.remove('bump');
          void cartCount.offsetWidth;
          cartCount.classList.add('bump');
        } else {
          console.error(data.message);
        }
      })
      .catch(console.error);
    });
  });

  // BUY NOW (updated to use add-to-cart.php and redirect to cart.php)
  document.querySelectorAll('.buy-btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault(); e.stopPropagation();
      const id = this.closest('.card').dataset.id;

      this.textContent = '⏳…';
      this.disabled = true;

      fetch('add-to-cart.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'dish_id=' + encodeURIComponent(id)
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          // redirect to cart page after adding to cart
          window.location.href = 'cart.php';
        } else {
          alert(data.message || 'Purchase failed.');
          btn.textContent = 'BUY NOW';
          btn.disabled = false;
        }
      })
      .catch(err => {
        console.error(err);
        alert('Network error.');
        btn.textContent = 'BUY NOW';
        btn.disabled = false;
      });
    });
  });

  // CATEGORY FILTER
  const categorySelect = document.getElementById('category');
  const cards = document.querySelectorAll('.card');
  const categoryParagraph = document.getElementById('category-paragraph');

  categorySelect.addEventListener('change', function () {
    const selected = this.value.toLowerCase();

    cards.forEach(card => {
      const cardCategory = card.dataset.category.toLowerCase();

      if (selected === 'all' || cardCategory === selected) {
        card.style.display = '';
      } else {
        card.style.display = 'none';
      }
    });

    if (categoryParagraph) {
      const formatted = selected.charAt(0).toUpperCase() + selected.slice(1);
      categoryParagraph.textContent = `${formatted} Category`;
    }
  });
});
