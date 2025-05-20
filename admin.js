// admin.js
document.addEventListener("DOMContentLoaded", () => {
    // Redirect to admin-menu.php when Menu button clicked
    const menuBtn = document.getElementById("menu-btn");
    if (menuBtn) {
        menuBtn.addEventListener("click", () => {
            window.location.href = "admin-menu.php";
        });
    }

    // Redirect to admin-add-dish.php when ADD NEW DISH clicked
    const addDishBtn = document.getElementById("add-dish-btn");
    if (addDishBtn) {
        addDishBtn.addEventListener("click", () => {
            window.location.href = "admin-add-dish.php";
        });
    }

    // Redirect to admin-about.php when About Page button clicked
    const aboutPageBtn = document.getElementById("about-page-btn");
    if (aboutPageBtn) {
        aboutPageBtn.addEventListener("click", () => {
            window.location.href = "admin-about.php";
        });
    }

    // Redirect to sales-report.php when Sales Report button clicked
    const salesReportBtn = document.getElementById("sales-report-btn");
    if (salesReportBtn) {
        salesReportBtn.addEventListener("click", () => {
            window.location.href = "sales-report.php";
        });
    }

    // Redirect to orders.php when Orders button clicked
    const ordersBtn = document.getElementById("orders-btn");
    if (ordersBtn) {
        ordersBtn.addEventListener("click", () => {
            window.location.href = "orders.php";
        });
    }

    // Redirect to admin-top-selling.php when this button is clicked
    const topSellingBtn = document.getElementById("top-selling-btn");
    if (topSellingBtn) {
        topSellingBtn.addEventListener("click", () => {
            window.location.href = "admin-top-selling.php";
        })
    }

    // Fetch admin data and update dashboard
    fetch("admin-data.php")
        .then(res => {
            if (!res.ok) throw new Error("Network response was not ok");
            return res.json();
        })
        .then(data => {
            // Total Sales
            document.getElementById("sales").textContent = 
                `₱${parseFloat(data.total_sales).toLocaleString()}`;

            // Total Orders
            document.getElementById("orders").textContent = 
                parseInt(data.total_orders).toLocaleString();

            // Best Seller Dish
            document.getElementById("best-dish").textContent = data.best_seller;

            // Recent Order
            if (data.recent_order) {
                const { dish_name, quantity, order_time } = data.recent_order;
                document.querySelector(".recent-order-name").innerHTML = `
                    <p>${dish_name}</p>
                    <p>${quantity}x</p>
                    <p id="time">${order_time}</p>
                `;
            }

            // Low Stock Warning
            const lowStockTable = document.querySelector(".low-stock table");
            lowStockTable.innerHTML = ""; // Clear any existing rows
            data.low_stocks.forEach(item => {
                const row = `<tr>
                    <td>${item.item_name}</td>
                    <td class="stock-count">${item.stock_count} left</td>
                </tr>`;
                lowStockTable.innerHTML += row;
            });

            // Customer Feedback (most recent)
            if (data.feedback) {
                // 1) Name & comment
                document.getElementById("customer-fb-name").textContent = 
                    `${data.feedback.fname} ${data.feedback.lname}`;
                document.getElementById("prof-email").textContent = 
                    `@${data.feedback.email}`;
                document.querySelector(".fb p").textContent = 
                    data.feedback.comment;

                // 2) Profile picture
                const picElement = document.getElementById("user-image-fb");
                if (data.feedback.profile_pic) {
                    picElement.src = 
                        "data:image/jpeg;base64," + data.feedback.profile_pic;
                } else {
                    picElement.src = "images/user.png";
                }

                // 3) (Optional) You could set star icons to reflect data.feedback.rating
                //    but that isn’t shown here.
            }

            // Customer Favorites (Top 4)
            const favContainer = document.querySelector(".customerf");
            favContainer.innerHTML = `<p class="title">Customer Favorites</p>`;
            data.favorites.forEach((dish, index) => {
                favContainer.innerHTML += `
                    <p>
                        ${index + 1}. ${dish.dish_name} 
                        <span class="ratings">${dish.ratings} ratings</span>
                    </p>`;
            });
        })
        .catch(err => console.error("Error fetching admin data:", err));
});
