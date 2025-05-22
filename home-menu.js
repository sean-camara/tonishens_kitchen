// home-menu.js
document.addEventListener('DOMContentLoaded', () => {
  const cartCount = document.getElementById('cart-count');
  const cards = document.querySelectorAll('.card');
  const categorySelect = document.getElementById('category-select');
  const categoryParagraph = document.getElementById('category-paragraph');

  /* 1) Fetch and display cart count */
  fetch('get-cart-count.php')
    .then(res => res.json())
    .then(data => {
      if (data.count > 0) {
        cartCount.textContent = data.count;
        cartCount.style.display = 'inline-block';
      }
    })
    .catch(() => {
      /* Fail silently if fetch fails */
    });

  /* 2) Animate cards with incremental delays */
  cards.forEach((card, index) => {
    card.style.animationDelay = `${index * 0.1 + 0.2}s`;
    card.classList.add('fade-in-up');
  });

  /* 3) Handle “Add to Cart” button clicks */
  document.querySelectorAll('.cart-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      const card = btn.closest('.card');
      const id = card.dataset.id;

      // Visual feedback
      btn.textContent = '✔ Added!';
      btn.style.background = '#28a745';
      btn.style.color = '#fff';

      setTimeout(() => {
        btn.textContent = 'ADD TO CART';
        btn.style.background = '';
        btn.style.color = '';
      }, 1200);

      // AJAX request to add to cart
      fetch('add-to-cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `dish_id=${encodeURIComponent(id)}`
      })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            cartCount.textContent = data.count;
            cartCount.style.display = 'inline-block';

            // Bump animation
            cartCount.classList.remove('bump');
            void cartCount.offsetWidth; // trigger reflow
            cartCount.classList.add('bump');
          } else {
            console.error(data.message);
          }
        })
        .catch(console.error);
    });
  });

  /* 4) Handle “Buy Now” button clicks */
  document.querySelectorAll('.buy-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      const card = btn.closest('.card');
      const id = card.dataset.id;

      // Indicate loading
      btn.textContent = '⏳…';
      btn.disabled = true;

      fetch('add-to-cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `dish_id=${encodeURIComponent(id)}`
      })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
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

  /* 5) Category filtering */
  categorySelect.addEventListener('change', function () {
    const selected = this.value.toLowerCase();

    cards.forEach(card => {
      const cardCategory = card.dataset.category.toLowerCase();
      card.style.display = (selected === 'all' || cardCategory === selected) ? 'flex' : 'none';
    });

    const labelText = (selected === 'all') ? 'All Categories'
      : selected.charAt(0).toUpperCase() + selected.slice(1);
    categoryParagraph.textContent = labelText;
  });
});
