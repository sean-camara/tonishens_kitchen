document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("feedback-form");
  const message = document.getElementById("feedback-message");

  // Handle star selection
  document.querySelectorAll(".dish").forEach(dish => {
    const stars = dish.querySelectorAll(".star");
    stars.forEach(star => {
      star.addEventListener("click", () => {
        const rating = parseInt(star.getAttribute("data-value"));
        stars.forEach((s, index) => {
          s.innerHTML = index < rating ? "★" : "☆";
        });
        dish.setAttribute("data-rating", rating);
      });
    });
  });

  form.addEventListener("submit", (e) => {
    e.preventDefault();

    const orderId = form.querySelector("input[name='order_id']").value;
    const comment = document.getElementById("global-comment").value.trim();
    const feedback = [];

    document.querySelectorAll(".dish").forEach(dish => {
      const rating = parseInt(dish.getAttribute("data-rating") || "0");
      if (rating > 0) {
        feedback.push({
          dish_id: dish.getAttribute("data-dish-id"),
          rating: rating
        });
      }
    });

    fetch("submit_feedback.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        order_id: orderId,
        comment: comment,
        feedback: feedback
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        message.style.display = "block";
        setTimeout(() => {
          window.location.href = "home.php";
        }, 2000);
      } else {
        alert("Failed to submit feedback. " + data.message);
      }
    })
    .catch(err => {
      alert("Error sending feedback.");
      console.error(err);
    });
  });
});
