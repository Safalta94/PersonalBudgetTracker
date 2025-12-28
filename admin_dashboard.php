<?php
session_start();
include('db.php');
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit;
}
// Get total number of users
$userQuery = "SELECT COUNT(*) AS total_users FROM users WHERE role = 'user'";
$userResult = $conn->query($userQuery);
$totalUsers = $userResult->fetch_assoc()['total_users'];

// Get total number of admins
$adminQuery = "SELECT COUNT(*) AS total_admins FROM users WHERE role = 'admin'";
$adminResult = $conn->query($adminQuery);
$totalAdmins = $adminResult->fetch_assoc()['total_admins'];

// Get all categories
$categoryQuery = "SELECT name, type FROM categories";
$categoryResult = $conn->query($categoryQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="sidebar/style.css">
    <link rel="stylesheet" href="Styles/admin_dash.css">
</head>
<body>
<?php include('sidebar/adminnav.html'); ?>

<div class="dashboard-container">
    <h2>Admin Dashboard</h2>
    <div class="stats-container">
        <div class="stats">Total Users: <?php echo $totalUsers; ?></div>
        <div class="stats">Total Admins: <?php echo $totalAdmins; ?></div>
    </div>
    
    <h3>Categories</h3>
    <div class="categories-container">
        <div class="category-box">
            <h4>Income Categories</h4>
            <ul>
                <?php
                $categoryResult->data_seek(0);
                $hasIncome = false;
                while ($row = $categoryResult->fetch_assoc()) {
                    if ($row["type"] == "income") {
                        echo "<li>" . htmlspecialchars($row["name"]) . "</li>";
                        $hasIncome = true;
                    }
                }
                if (!$hasIncome) echo "<li>No income categories found.</li>";
                ?>
            </ul>
        </div>
        <div class="category-box">
            <h4>Expense Categories</h4>
            <ul>
                <?php
                $categoryResult->data_seek(0);
                $hasExpense = false;
                while ($row = $categoryResult->fetch_assoc()) {
                    if ($row["type"] == "expenses") {
                        echo "<li>" . htmlspecialchars($row["name"]) . "</li>";
                        $hasExpense = true;
                    }
                }
                if (!$hasExpense) echo "<li>No expense categories found.</li>";
                ?>
            </ul>
        </div>
    </div>
</div>
</body>
</html>

<?php
$conn->close(); // Close the database connection
?>
