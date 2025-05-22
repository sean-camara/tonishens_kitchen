document.addEventListener("DOMContentLoaded", () => {
    const filter = document.getElementById("filter");
    const exportBtn = document.getElementById("export-btn");
    const tbody = document.getElementById("top-selling-body");
    const ctx = document.getElementById("topSellingChart").getContext("2d");
    let chart;

    function fetchTopSelling(days = "all") {
        fetch(`get-top-selling.php?days=${days}`)
            .then(res => res.json())
            .then(data => {
                // 1) Populate Table
                tbody.innerHTML = "";
                data.forEach(dish => {
                    const row = `
                        <tr>
                            <td>
                                <img src="data:image/jpeg;base64,${dish.image}" class="dish-img" alt="${dish.dish_name}">
                            </td>
                            <td>${dish.dish_name}</td>
                            <td>${dish.total_sold}</td>
                        </tr>`;
                    tbody.innerHTML += row;
                });

                // 2) Update Chart
                const labels = data.map(d => d.dish_name);
                const values = data.map(d => d.total_sold);

                if (chart) chart.destroy();
                chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Total Sold',
                            data: values,
                            backgroundColor: '#FF7750'
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    color: '#2D2D2D'
                                }
                            },
                            x: {
                                ticks: {
                                    color: '#2D2D2D'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                labels: {
                                    color: '#2D2D2D'
                                }
                            }
                        }
                    }
                });
            })
            .catch(err => {
                console.error("Error fetching top selling data:", err);
            });
    }

    filter.addEventListener("change", () => {
        fetchTopSelling(filter.value);
    });

    exportBtn.addEventListener("click", () => {
        const days = filter.value;
        window.location.href = `export-top-selling.php?days=${days}`;
    });

    // Initial load
    fetchTopSelling();
});
