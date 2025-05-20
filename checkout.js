document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('checkout-form');
  const popup = document.getElementById('order-popup');
  const okBtn = document.getElementById('popup-ok-btn');

  form.addEventListener('submit', function(e) {
    e.preventDefault(); // prevent default submit

    const formData = new FormData(form);

    fetch(form.action, {
      method: 'POST',
      body: formData,
    })
    .then(response => response.json())
    .then(data => {
      if(data.success){
        popup.style.display = 'flex';  // show popup

        okBtn.onclick = function() {
          window.location.href = 'feedback.php?order_id=' + encodeURIComponent(data.order_id);
        };
      } else {
        alert('Failed to place order. Please try again.');
      }
    })
    .catch(err => {
      alert('There was an error submitting your order. Please try again.');
      console.error(err);
    });
  });
});
