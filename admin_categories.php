<?php
session_start();
include('db.php'); // Include the database connection

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Handle add/update before output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $type = $_POST['type'];

    // Check if category with the same name and type already exists
    $check_query = "SELECT id FROM categories WHERE name='$name' AND type='$type'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0 && !$id) {
        echo "<script>alert('Category with the same name and type already exists!');</script>";
    } else {
        if ($id) {
            $query = "UPDATE categories SET name='$name', type='$type' WHERE id=$id";
        } else {
            $query = "INSERT INTO categories (name, type) VALUES ('$name', '$type')";
        }
        mysqli_query($conn, $query);
        header("Location: admin_categories.php");
        exit;  // Ensure no further output
    }
}

// Handle delete before output
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Check if the category is linked in income or expenses tables
    $check_income = "SELECT id FROM income WHERE category_id = $id";
    $check_expenses = "SELECT id FROM expenses WHERE category_id = $id";

    $income_result = mysqli_query($conn, $check_income);
    $expenses_result = mysqli_query($conn, $check_expenses);

    if (mysqli_num_rows($income_result) > 0 || mysqli_num_rows($expenses_result) > 0) {
        echo "<script>alert('Error: Cannot delete category linked to transactions in income or expenses.');</script>";
    } else {
        mysqli_query($conn, "DELETE FROM categories WHERE id=$id");
        header("Location: admin_categories.php");
        exit;
    }
}



// Fetch category details when editing
$edit_id = isset($_GET['edit']) ? $_GET['edit'] : null;
$edit_category = null;
if ($edit_id) {
    $edit_query = "SELECT * FROM categories WHERE id='$edit_id'";
    $edit_result = mysqli_query($conn, $edit_query);
    $edit_category = mysqli_fetch_assoc($edit_result);
}

// Fetch all categories
$query = "SELECT * FROM categories ORDER BY FIELD(type, 'income', 'expenses'), name ASC";
$result = mysqli_query($conn, $query);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management</title>
    <link rel="stylesheet" href="sidebar/style.css">
    <style>
        body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f8f9fa;
}

.container {
    max-width: 900px;
    margin: 40px auto;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
    text-align: center;
}

/* Centering all headings */
h2, h3 {
    color: #333;
    text-align: center;
    margin: 20px 0px;
}

/* Adjust form size */
.add-form {
    display: flex;
    flex-direction: column;
    gap: 12px;
    width: 60%;
    margin: 0 auto 30px auto; /* Centers the form */
    padding: 20px;
    background: #f1f1f1;
    border-radius: 8px;
}

.add-form input, 
.add-form select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
}

.add-form button {
    background-color: #28a745;
    color: white;
    padding: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: 0.3s ease-in-out;
}

.add-form button:hover {
    background-color: #218838;
}

/* Table Styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 30px;
}

th, td {
    border: 1px solid #ddd;
    padding: 12px;
    text-align: center;
}

th {
    background: #007bff;
    color: white;
    font-size: 18px;
}

td {
    font-size: 16px;
}

.btn {
    padding: 6px 12px;
    text-decoration: none;
    border-radius: 5px;
    margin: 5px;
    display: inline-block;
}

.btn.edit {
    background: #ffc107;
    color: #333;
}

.btn.delete {
    background: #dc3545;
    color: white;
}

.btn.edit:hover {
    background: #e0a800;
}

.btn.delete:hover {
    background: #c82333;
}

    </style>
</head>

<body>
    <?php include('sidebar/adminnav.html'); ?>
    <h2>Category Management</h2>
    <h3><?php echo $edit_id ? "Update Category" : "Create Category"; ?></h3>
    <form method="POST" class="add-form">
        <input type="hidden" name="id" value="<?php echo $edit_id ? $edit_id : ''; ?>">
        <input type="text" name="name" placeholder="Category Name" required
            value="<?php echo $edit_id ? htmlspecialchars($edit_category['name']) : ''; ?>">
        <select name="type" required>
            <option value="income" <?php echo ($edit_id && $edit_category['type'] == 'income') ? 'selected' : ''; ?>>
                Income</option>
            <option value="expenses" <?php echo ($edit_id && $edit_category['type'] == 'expenses') ? 'selected' : ''; ?>>
                Expenses</option>
        </select>
        <button type="submit"><?php echo $edit_id ? "Update" : "Save"; ?></button>
    </form>
    <table>
        <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['type']); ?></td>
                <td>
                    <a href="?edit=<?php echo $row['id']; ?>" class="btn edit">Edit</a>
                    <a href="?delete=<?php echo $row['id']; ?>" class="btn delete"
                        onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </table>


</body>

</html>