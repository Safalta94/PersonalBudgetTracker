<?php 
session_start();
include('db.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get logged-in user ID
$user_id = $_SESSION['user_id'];

// Fetch totals
$totalIncome = $conn->query("SELECT SUM(amount) AS total FROM income WHERE user_id = $user_id")->fetch_assoc()['total'];
$totalExpenses = $conn->query("SELECT SUM(amount) AS total FROM expenses WHERE user_id = $user_id")->fetch_assoc()['total'];

// Set default totals to 0 if NULL
$totalIncome = $totalIncome ?? 0;
$totalExpenses = $totalExpenses ?? 0;

// Fetch recent transactions
$transactions = $conn->query("
    SELECT date, description, amount, 
           (SELECT name FROM categories WHERE id = income.category_id) AS category, 
           'income' AS type
    FROM income 
    WHERE user_id = $user_id
    UNION ALL
    SELECT date, description, amount, 
           (SELECT name FROM categories WHERE id = expenses.category_id) AS category, 
           'expense' AS type
    FROM expenses 
    WHERE user_id = $user_id
    ORDER BY date DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | Budget Tracker</title>
  <!-- Corrected paths for CSS -->
  <link rel="stylesheet" href="dashboard/dashboard.css">
  <link rel="stylesheet" href="sidebar/style.css">
  <link rel="stylesheet" href="footer/footer.css">
</head>
<body>
  <!-- Include Sidebar -->
  <?php include('sidebar/index.html'); ?>

  <!-- Main Content -->
  <div class="content">
    <div class="dashboard">
      <h1>Welcome to Budget Tracker</h1>
      <div class="summary">
        <div class="card">
          <h2>Total Income</h2>
          <p id="total-income">$<?php echo number_format($totalIncome, 2); ?></p>
        </div>
        <div class="card">
          <h2>Total Expenses</h2>
          <p id="total-expenses">-$<?php echo number_format($totalExpenses, 2); ?></p>
        </div>
        <div class="card">
          <h2>Balance</h2>
          <p id="balance">$<?php echo number_format(($totalIncome - $totalExpenses), 2); ?></p>
        </div>
      </div>
      <div class="recent-transactions">
        <h2>Recent Transactions</h2>
        <table>
          <thead>
            <tr>
              <th>Date</th>
              <th>Description</th>
              <th>Category</th>
              <th>Type</th>
              <th>Amount</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($transactions as $transaction): ?>
              <tr>
                <td><?php echo $transaction['date']; ?></td>
                <td><?php echo $transaction['description']; ?></td>
                <td><?php echo $transaction['category']; ?></td>
                <td><?php echo ucfirst($transaction['type']); ?></td>
                <td><?php echo $transaction['type'] === 'income' ? '+' : '-'; ?>$<?php echo number_format($transaction['amount'], 2); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Include Footer -->
  <?php include('footer/footer.html'); ?>

</body>
</html>
