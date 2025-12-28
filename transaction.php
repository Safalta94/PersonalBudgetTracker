<?php
include('db.php');  // Include your database connection

session_start();    // Start session to get the user_id
if (!isset($_SESSION['user_id'])) {
  // Redirect to login page if not logged in
  header('Location: login.php');
  exit();
}

// Set default sorting parameters
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'date';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Define allowed sorting options
$allowed_sort = ['date', 'amount'];
$allowed_order = ['ASC', 'DESC'];

// Validate user input to prevent SQL injection
if (!in_array($sort_by, $allowed_sort)) $sort_by = 'date';
if (!in_array($order, $allowed_order)) $order = 'DESC';

// SQL query to fetch transactions (incomes and expenses)
$sql = "
    SELECT 
        'Income' AS type, 
        i.amount, 
        i.date, 
        i.description, 
        c.name AS category_name
    FROM income i
    JOIN categories c ON i.category_id = c.id
    WHERE i.user_id = ?

    UNION ALL

    SELECT 
        'Expenses' AS type, 
        e.amount, 
        e.date, 
        e.description, 
        c.name AS category_name
    FROM expenses e
    JOIN categories c ON e.category_id = c.id
    WHERE e.user_id = ?

    ORDER BY $sort_by $order
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $_SESSION['user_id'], $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Transactions</title>
    <link rel="stylesheet" href="Styles/transaction.css">
    <link rel="stylesheet" href="sidebar/style.css">
    <link rel="stylesheet" href="footer/footer.css">
</head>
<body>
    <?php include('sidebar/index.html'); ?>
    <section>
        <h2>All Transactions</h2>
        <table>
            <thead>
                <tr>
                    <th><a href="?sort_by=type&order=<?php echo $sort_by == 'type' && $order == 'ASC' ? 'DESC' : 'ASC'; ?>">Type</a></th>
                    <th><a href="?sort_by=category_name&order=<?php echo $sort_by == 'category_name' && $order == 'ASC' ? 'DESC' : 'ASC'; ?>">Category</a></th>
                    <th><a href="?sort_by=amount&order=<?php echo $sort_by == 'amount' && $order == 'ASC' ? 'DESC' : 'ASC'; ?>">Amount</a></th>
                    <th><a href="?sort_by=date&order=<?php echo $sort_by == 'date' && $order == 'ASC' ? 'DESC' : 'ASC'; ?>">Date</a></th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['type']; ?></td>
                        <td><?php echo ucfirst($row['category_name']); ?></td>
                        <td>$<?php echo number_format($row['amount'], 2); ?></td>
                        <td><?php echo $row['date']; ?></td>
                        <td><?php echo $row['description']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </section>
    <?php include('footer/footer.html'); ?>
</body>
</html>
