<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Sales Reports</h3>
    <button class="btn btn-sm btn-outline-secondary" onclick="renderReport()">
        <i class="bi bi-arrow-clockwise"></i> Refresh
    </button>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title text-muted small mb-3">REVENUE (LAST 30 DAYS)</h5>
                <canvas id="salesChart" height="150"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="text-muted small fw-bold">TOTAL REVENUE (Visible)</div>
                <h2 class="text-primary mt-2" id="totalRevenue">RM 0.00</h2>
            </div>
        </div>
        <div class="card shadow-sm">
             <div class="card-body">
                <div class="text-muted small fw-bold">BEST SALES DAY</div>
                <h4 class="mt-2" id="bestDay">—</h4>
                <div class="text-success small" id="bestDayTotal">RM 0.00</div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let chartInstance = null;

async function renderReport() {
    try {
        const res = await fetch('/api/reports.php');
        const data = await res.json();

        if (!data || data.length === 0) {
            console.warn("No data for reports");
            return;
        }

        // Prepare Arrays for Chart
        const labels = data.map(d => d.date);
        const values = data.map(d => parseFloat(d.total_sales));

        // Calculate Summary Stats
        const totalRev = values.reduce((a, b) => a + b, 0);
        document.getElementById('totalRevenue').innerText = 'RM ' + totalRev.toFixed(2);

        // Find Best Day
        let maxVal = -1;
        let maxDay = '';
        data.forEach(d => {
            if (parseFloat(d.total_sales) > maxVal) {
                maxVal = parseFloat(d.total_sales);
                maxDay = d.date;
            }
        });
        document.getElementById('bestDay').innerText = maxDay;
        document.getElementById('bestDayTotal').innerText = 'RM ' + maxVal.toFixed(2);

        // Render Chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        
        // Destroy old chart if refreshing
        if (chartInstance) chartInstance.destroy();

        chartInstance = new Chart(ctx, {
            type: 'line', // 'line' is often better for trends than 'bar'
            data: {
                labels: labels,
                datasets: [{
                    label: 'Daily Revenue (RM)',
                    data: values,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3 // Smooth curves
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) { return 'RM ' + value; }
                        }
                    }
                }
            }
        });

    } catch (err) {
        console.error("Failed to load report", err);
    }
}

renderReport();
</script>