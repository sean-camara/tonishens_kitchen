document.addEventListener('DOMContentLoaded', function () {
    const cartCount = document.getElementById('cart-count');

    // Initial cart count fetch
    fetch('get-cart-count.php')
        .then(res => res.json())
        .then(data => {
            if (data.count > 0) {
                cartCount.textContent = data.count;
                cartCount.style.display = 'inline-block';
            }
        });

    // Handle Add to Cart clicks
    document.querySelectorAll('.cart-btn').forEach(button => {
        button.addEventListener('click', function () {
            const card = this.closest('.card');
            const dishId = card.getAttribute('data-id');

            // Button visual feedback
            button.textContent = "✔ Added!";
            button.style.backgroundColor = "#28a745";
            setTimeout(() => {
                button.textContent = "ADD TO CART";
                button.style.backgroundColor = "#FBF9F8";
                button.style.color = "#2D2D2D";
            }, 1200);

            // AJAX request
            fetch('add-to-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'dish_id=' + encodeURIComponent(dishId)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    cartCount.textContent = data.count;
                    cartCount.style.display = 'inline-block';

                    // Bump animation
                    cartCount.classList.remove('bump');
                    void cartCount.offsetWidth;
                    cartCount.classList.add('bump');
                }
            });
        });
    });

    // Handle Buy Now clicks
    document.querySelectorAll('.buy-btn').forEach(button => {
        button.addEventListener('click', function () {
            const card = this.closest('.card');
            const dishId = card.getAttribute('data-id');

            // Optional visual feedback (like "Processing...")
            button.textContent = "Processing...";
            button.disabled = true;

            // Add to cart then redirect
            fetch('add-to-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'dish_id=' + encodeURIComponent(dishId)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'cart.php';
                } else {
                    // Re-enable button if failed
                    button.textContent = "Buy";
                    button.disabled = false;
                }
            });
        });
    });
});
