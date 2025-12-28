<?php
include('db.php');  // Include your database connection

session_start();  // Assuming you're using sessions for user login
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: login.php');
    exit();
}

// Fetch categories with type 'income' from the database
$sql = "SELECT * FROM categories WHERE type = 'income'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$categories = $stmt->get_result();

$income = null;
// Fetch income details if editing
if (isset($_GET['edit'])) {
    $income_id = $_GET['edit'];
    $sql = "SELECT * FROM income WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $income_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $income = $result->fetch_assoc();
    }
}

// Handle adding new income
if (isset($_POST['add_income'])) {
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $description = $_POST['description'];

    if ($amount < 0) {
        echo "Error: Income amount cannot be negative.";
    } else {
        $sql = "INSERT INTO income (user_id, category_id, amount, date, description) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiss", $_SESSION['user_id'], $category, $amount, $date, $description);
        $stmt->execute();
        echo "Income added successfully!";
    }
}

// Handle updating income
if (isset($_POST['update_income'])) {
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $description = $_POST['description'];
    $id = $_POST['income_id'];

    if ($amount < 0) {
        echo "Error: Income amount cannot be negative.";
    } else {
        $sql = "UPDATE income SET category_id = ?, amount = ?, date = ?, description = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("idssii", $category, $amount, $date, $description, $id, $_SESSION['user_id']);
        if ($stmt->execute()) {
            echo "Income updated successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}

// Handle deleting income
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM income WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $_SESSION['user_id']);
    if ($stmt->execute()) {
        echo "Income deleted successfully!";
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
    <title>Income Management</title>
    <link rel="stylesheet" href="Styles/Income-Expense.css">
    <link rel="stylesheet" href="sidebar/style.css">
    <link rel="stylesheet" href="footer/footer.css">
</head>
<body>
    <?php include('sidebar/index.html'); ?>
    <section>
        <h2><?php echo isset($income) ? 'Edit Income' : 'Add Income'; ?></h2>
        <form method="POST" action="income.php">
            <label for="category">Category</label>
            <select id="category" name="category" required>
                <?php while ($row = $categories->fetch_assoc()) { ?>
                    <option value="<?php echo $row['id']; ?>" <?php echo isset($income) && $income['category_id'] == $row['id'] ? 'selected' : ''; ?>>
                        <?php echo ucfirst($row['name']); ?>
                    </option>
                <?php } ?>
            </select>
            <label for="amount">Amount</label>
            <input type="number" id="amount" name="amount" value="<?php echo isset($income) ? $income['amount'] : ''; ?>" required>
            <label for="date">Date</label>
            <input type="date" id="date" name="date" value="<?php echo isset($income) ? $income['date'] : ''; ?>" required max="<?php echo date('Y-m-d'); ?>">
            <label for="description">Description</label>
            <textarea id="description" name="description"> <?php echo isset($income) ? $income['description'] : ''; ?></textarea>
            <?php if (isset($income)): ?>
                <input type="hidden" name="income_id" value="<?php echo $income['id']; ?>">
                <button type="submit" name="update_income">Update Income</button>
            <?php else: ?>
                <button type="submit" name="add_income">Add Income</button>
            <?php endif; ?>
        </form>
        <h2>Income Records</h2>
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
                $sql = "SELECT income.*, categories.name AS category_name FROM income 
                        JOIN categories ON income.category_id = categories.id 
                        WHERE income.user_id = ?";
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
                                <a href='income.php?edit={$row['id']}'>Edit</a> | 
                                <a href='income.php?delete={$row['id']}'>Delete</a>
                            </td>
                        </tr>";
                }
                ?>
            </tbody>
        </table>
    </section>
    <?php include('footer/footer.html'); ?>
</body>
</html>
