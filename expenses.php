<?php
include('db.php');  // Include your database connection

session_start();  // Assuming you're using sessions for user login
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: login.php');
    exit();
}

// Fetch categories with type 'expenses' from the database
$sql = "SELECT * FROM categories WHERE type = 'expenses'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$categories = $stmt->get_result();

// Handle adding new expenses
if (isset($_POST['add_expenses'])) {
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $description = $_POST['description'];

    $sql = "INSERT INTO expenses (user_id, category_id, amount, date, description) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiss", $_SESSION['user_id'], $category, $amount, $date, $description);
    $stmt->execute();
    echo "Expenses added successfully!";
}

// Handle editing expenses
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $sql = "SELECT * FROM expenses WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $expense = $result->fetch_assoc();
}

// Handle updating expenses
if (isset($_POST['update_expenses'])) {
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $description = $_POST['description'];
    $id = $_POST['expenses_id'];

    $sql = "UPDATE expenses SET category_id = ?, amount = ?, date = ?, description = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idssi", $category, $amount, $date, $description, $id);
    if ($stmt->execute()) {
        echo "Expenses updated successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Handle deleting expenses
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM expenses WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "Expenses deleted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expenses Management</title>
    <link rel="stylesheet" href="Styles/Income-Expense.css">
    <link rel="stylesheet" href="sidebar/style.css">
    <link rel="stylesheet" href="footer/footer.css">
</head>

<body>
    <!-- Include Sidebar -->
    <?php include('sidebar/index.html'); ?>

    <section>
        <!-- Add Expenses Form -->
        <h2><?php echo isset($expense) ? 'Edit Expenses' : 'Add Expenses'; ?></h2>
        <form method="POST" action="expenses.php">
            <label for="category">Category</label>
            <select id="category" name="category" required>
                <?php while ($row = $categories->fetch_assoc()) { ?>
                    <option value="<?php echo $row['id']; ?>" <?php echo isset($expenses) && $expenses['category_id'] == $row['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars(ucfirst($row['name'])); ?>
                    </option>
                <?php } ?>
            </select>

            <label for="amount">Amount</label>
            <input type="number" id="amount" name="amount"
                value="<?php echo isset($expense) ? $expense['amount'] : ''; ?>" required>

            <label for="date">Date</label>
            <input type="date" id="date" name="date" value="<?php echo isset($expense) ? $expense['date'] : ''; ?>"
                required max="<?php echo date('Y-m-d'); ?>">


            <label for="description">Description</label>
            <textarea id="description"
                name="description"><?php echo isset($expense) ? $expense['description'] : ''; ?></textarea>

            <?php if (isset($expense)): ?>
                <input type="hidden" name="expenses_id" value="<?php echo $expense['id']; ?>">
                <button type="submit" name="update_expenses">Update Expenses</button>
            <?php else: ?>
                <button type="submit" name="add_expenses">Add Expenses</button>
            <?php endif; ?>
        </form>

        <h2>Expenses Records</h2>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT expenses.*, categories.name AS category_name FROM expenses 
                        JOIN categories ON expenses.category_id = categories.id 
                        WHERE expenses.user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['category_name']}</td>
                            <td>\${$row['amount']}</td>
                            <td>{$row['date']}</td>
                            <td>{$row['description']}</td>
                            <td>
                                <a href='expenses.php?edit={$row['id']}'>Edit</a> | 
                                <a href='expenses.php?delete={$row['id']}'>Delete</a>
                            </td>
                        </tr>";
                }
                ?>
            </tbody>
        </table>
    </section>

    <!-- Include Footer -->
    <?php include('footer/footer.html'); ?>

</body>

</html>