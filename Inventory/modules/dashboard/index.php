<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

$title = "Dashboard";

// Modified functions to get yesterday's data
$productCount = getProductCount();
$lowStockCount = getLowStockCount();
$salesToday = getSalesForDate(date('Y-m-d'));
$purchasesToday = getPurchasesForDate(date('Y-m-d'));

// New data for additional charts
$monthlySales = getMonthlySales();
$stockLevels = getStockLevels();
$categorySales = getCategorySales();
$topProducts = getTopSellingProducts(5);

include '../../includes/header.php';
?>

<div class="dashboard-cards">
    <div class="dashboard-card">
        <i class="fas fa-box"></i>
        <div class="value"><?php echo $productCount; ?></div>
        <div class="label">Total Products</div>
    </div>
    <div class="dashboard-card">
        <i class="fas fa-exclamation-triangle"></i>
        <div class="value"><?php echo $lowStockCount; ?></div>
        <div class="label">Low Stock Items</div>
    </div>
    <div class="dashboard-card">
        <i class="fas fa-cash-register"></i>
        <div class="value">₱ <?php echo number_format($salesToday, 2); ?></div>
        <div class="label">Today's Sales</div>
    </div>
    <div class="dashboard-card">
        <i class="fas fa-shopping-cart"></i>
        <div class="value">₱ <?php echo number_format($purchasesToday, 2); ?></div>
        <div class="label">Today's Purchases</div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Seven(7)-Day Sales</h3>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Top Selling Products</h3>
            </div>
            <div class="card-body">
                <canvas id="topProductsChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Monthly Sales (<?php echo date('Y') ?>)</h3>
            </div>
            <div class="card-body">
                <canvas id="monthlySalesChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Stock Levels</h3>
            </div>
            <div class="card-body">
                <canvas id="stockLevelsChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sales by Category</h3>
            </div>
            <div class="card-body">
                <canvas id="categorySalesChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">Today's Sales (<?php echo date('M d, Y') ?>)</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Time</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (getSalesForDate(date('Y-m-d'), true) as $sale) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($sale['invoice_no']); ?></td>
                    <td><?php echo date('h:i A', strtotime($sale['created_at'])); ?></td>
                    <td><?php echo htmlspecialchars($sale['customer_name'] ?: 'Walk-in'); ?></td>
                    <td>₱<?php echo number_format($sale['total_amount'], 2); ?></td>
                    <td>
                        <span class="badge badge-success">Completed</span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Sales chart (7 days)
const salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: <?php 
            $dates = [];
            for ($i = 6; $i >= 0; $i--) {
                $dates[] = date('D M j', strtotime("-$i days"));
            }
            echo json_encode($dates);
        ?>,
        datasets: [{
            label: 'Sales Amount',
            data: <?php 
                $salesData = [];
                for ($i = 6; $i >= 0; $i--) {
                    $date = date('Y-m-d', strtotime("-$i days"));
                    $salesData[] = (float)getSalesForDate($date);
                }
                echo json_encode($salesData);
            ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return '₱' + context.raw.toLocaleString();
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₱' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Top products chart
const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
new Chart(topProductsCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($topProducts, 'name')); ?>,
        datasets: [{
            label: 'Units Sold',
            data: <?php echo json_encode(array_column($topProducts, 'total_sold')); ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Monthly Sales Chart
const monthlySalesCtx = document.getElementById('monthlySalesChart').getContext('2d');
new Chart(monthlySalesCtx, {
    type: 'bar',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'Monthly Sales',
            data: <?php echo json_encode($monthlySales); ?>,
            backgroundColor: 'rgba(153, 102, 255, 0.7)',
            borderColor: 'rgba(153, 102, 255, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return '₱' + context.raw.toLocaleString();
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₱' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Stock Levels Chart
const stockLevelsCtx = document.getElementById('stockLevelsChart').getContext('2d');
new Chart(stockLevelsCtx, {
    type: 'doughnut',
    data: {
        labels: ['Low Stock', 'In Stock', 'Overstock'],
        datasets: [{
            data: <?php echo json_encode($stockLevels); ?>,
            backgroundColor: [
                'rgba(255, 99, 132, 0.7)',
                'rgba(75, 192, 192, 0.7)',
                'rgba(255, 206, 86, 0.7)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right'
            }
        }
    }
});

// Category Sales Chart
const categorySalesCtx = document.getElementById('categorySalesChart').getContext('2d');
new Chart(categorySalesCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($categorySales, 'category_name')); ?>,
        datasets: [{
            label: 'Sales by Category',
            data: <?php echo json_encode(array_column($categorySales, 'total_sales')); ?>,
            backgroundColor: 'rgba(255, 159, 64, 0.7)',
            borderColor: 'rgba(255, 159, 64, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return '₱' + context.raw.toLocaleString();
                    }
                }
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₱' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>

<?php include '../../includes/footer.php'; ?>