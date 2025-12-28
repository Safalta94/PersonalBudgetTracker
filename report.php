<?php
// Include database connection and start session
include('db.php');
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch user ID from session
$user_id = $_SESSION['user_id'];

// Fetch filter type (month or year), default is 'monthly'
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'monthly';

// Adjust SQL queries based on the selected filter
if ($filter === 'yearly') {
    $timeFormat = "YEAR(e.date)";
    $timeLabel = "year";
} else { // Default: monthly
    $timeFormat = "MONTH(e.date)";
    $timeLabel = "month";
}

// Query for income and expenses based on the filter
$reportQuery = "
    SELECT 
        $timeFormat AS $timeLabel, 
        COALESCE((SELECT SUM(i.amount) FROM income i WHERE i.user_id = ? AND $timeFormat = $timeFormat), 0) AS total_income,
        COALESCE(SUM(e.amount), 0) AS total_expenses
    FROM expenses e
    WHERE e.user_id = ?
    GROUP BY $timeFormat
    ORDER BY $timeFormat";

$stmt = $conn->prepare($reportQuery);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$reportResult = $stmt->get_result();
$reportData = [];
while ($row = $reportResult->fetch_assoc()) {
    $reportData[] = $row;
}

// Query for spending breakdown by category
$categoryQuery = "
    SELECT 
        c.name AS category, 
        SUM(e.amount) AS total 
    FROM expenses e
    JOIN categories c ON e.category_id = c.id
    WHERE e.user_id = ?
    GROUP BY e.category_id";

$stmt = $conn->prepare($categoryQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$categoryResult = $stmt->get_result();
$categoryData = [];
while ($row = $categoryResult->fetch_assoc()) {
    $categoryData[$row['category']] = $row['total'];
}

// Query for savings over time based on the filter
$savingsQuery = "
    SELECT 
        $timeFormat AS $timeLabel, 
        (COALESCE((SELECT SUM(i.amount) FROM income i WHERE i.user_id = ? AND $timeFormat = $timeFormat), 0) 
        - COALESCE(SUM(e.amount), 0)) AS savings
    FROM expenses e
    WHERE e.user_id = ?
    GROUP BY $timeFormat
    ORDER BY $timeFormat";

$stmt = $conn->prepare($savingsQuery);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$savingsResult = $stmt->get_result();
$savingsData = [];
while ($row = $savingsResult->fetch_assoc()) {
    $savingsData[] = $row['savings'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link rel="stylesheet" href="Styles/report.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="sidebar/style.css">
    <link rel="stylesheet" href="footer/footer.css">
</head>

<body>
    <?php include('sidebar/index.html'); ?>

    <div class="content">
        <div class="report-container">
            <h2>Analytics & Reports</h2>

            <!-- Filter Options -->
            <div class="filter-container">
                <form method="GET" action="report.php">
                    <label for="filter">View By:</label>
                    <select name="filter" id="filter" onchange="this.form.submit()">
                        <option value="monthly" <?php if ($filter === 'monthly')
                            echo 'selected'; ?>>Monthly</option>
                        <option value="yearly" <?php if ($filter === 'yearly')
                            echo 'selected'; ?>>Yearly</option>
                    </select>
                </form>
            </div>
            <div class="chart-row">
                <!-- Monthly or Yearly Income vs Expenses Chart -->
                <div class="chart-container">
                    <h3><?php echo ucfirst($filter); ?> Income vs Expenses</h3>
                    <canvas id="incomeVsExpensesChart" width="400" height="200"></canvas>
                </div>
                <!-- Savings Rate Over Time Chart -->
                <div class="chart-container">
                    <h3><?php echo ucfirst($filter); ?> Savings Rate</h3>
                    <canvas id="savingsRateChart" width="400" height="200"></canvas>
                </div>

            </div>
            <!-- Spending Breakdown by Category Chart -->
            <div class="chart-container">
                <h3>Spending Breakdown by Category</h3>
                <canvas id="categoryBreakdownChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <?php include('footer/footer.html'); ?>

    <script>
        // Data from PHP
        const reportData = <?php echo json_encode($reportData); ?>;
        const categoryData = <?php echo json_encode($categoryData); ?>;
        const savingsData = <?php echo json_encode($savingsData); ?>;

        // Income vs Expenses Chart
        function renderIncomeVsExpensesChart() {
            const ctx = document.getElementById("incomeVsExpensesChart").getContext("2d");
            new Chart(ctx, {
                type: "bar",
                data: {
                    labels: reportData.map(data => `${"<?php echo ucfirst($filter); ?>"}` + " " + data.<?php echo $timeLabel; ?>),
                    datasets: [
                        { label: "Income", data: reportData.map(data => data.total_income), backgroundColor: "#379683" },
                        { label: "Expenses", data: reportData.map(data => data.total_expenses), backgroundColor: "#e63946" },
                    ]
                },
                options: { responsive: true }
            });
        }

        // Spending Breakdown by Category Chart
        function renderCategoryBreakdownChart() {
            const ctx = document.getElementById("categoryBreakdownChart").getContext("2d");
            new Chart(ctx, {
                type: "pie",
                data: {
                    labels: Object.keys(categoryData),
                    datasets: [{
                        data: Object.values(categoryData),
                        backgroundColor: ["#ffadad", "#ffd6a5", "#fdffb6", "#caffbf"]
                    }]
                },
                options: { responsive: true }
            });
        }

        // Savings Rate Over Time Chart
        function renderSavingsRateChart() {
            const ctx = document.getElementById("savingsRateChart").getContext("2d");
            new Chart(ctx, {
                type: "line",
                data: {
                    labels: reportData.map(data => `${"<?php echo ucfirst($filter); ?>"}` + " " + data.<?php echo $timeLabel; ?>),
                    datasets: [{
                        label: "Savings Rate",
                        data: savingsData,
                        backgroundColor: "rgba(83, 144, 217, 0.2)",
                        borderColor: "#379683",
                        borderWidth: 2,
                        fill: true,
                    }]
                },
                options: { responsive: true }
            });
        }

        // Initialize charts
        renderIncomeVsExpensesChart();
        renderCategoryBreakdownChart();
        renderSavingsRateChart();
    </script>
</body>

</html>