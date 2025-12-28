<?php
session_start();
include('db.php'); // Include the database connection

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Handle user creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Securely hash the password
    $role = $_POST['role'];

    $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $email, $password, $role);
    if ($stmt->execute()) {
        echo "<script>alert('User created successfully');</script>";
    } else {
        echo "<script>alert('Error creating user');</script>";
    }
    $stmt->close();
}
// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $userId = $_POST['id'];

    // Check if the user has associated expenses or income
    $sql = "SELECT 
                (SELECT COUNT(*) FROM expenses WHERE user_id = ?) AS expenseCount,
                (SELECT COUNT(*) FROM income WHERE user_id = ?) AS incomeCount";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $userId);
    $stmt->execute();
    $stmt->bind_result($expenseCount, $incomeCount);
    $stmt->fetch();
    $stmt->close();

    if ($expenseCount > 0 || $incomeCount > 0) {
        echo "<script>alert('Error: Cannot delete user with linked transactions (expenses or income).');</script>";
    } else {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        if ($stmt->execute()) {
            echo "<script>alert('User deleted successfully');</script>";
        } else {
            echo "<script>alert('Error deleting user');</script>";
        }
        $stmt->close();
    }
}

// Handle user update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $userId = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    // Check if the new email already exists for another user
    $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $email, $userId);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        echo "<script>alert('Error: This email is already in use by another user.');</script>";
    } else {
        // Proceed with updating user if email is unique
        $sql = "UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $name, $email, $role, $userId);

        if ($stmt->execute()) {
            echo "<script>alert('User updated successfully');</script>";
            echo "<script>window.location.href = window.location.href;</script>"; // Refresh page
        } else {
            echo "<script>alert('Error updating user');</script>";
        }
    }
    $stmt->close();
}

// Handle password update   
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $userId = $_POST['id'];
    $newPassword = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $sql = "UPDATE users SET password = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $newPassword, $userId);

    if ($stmt->execute()) {
        echo "<script>alert('Password updated successfully');</script>";
        echo "<script>window.location.href = window.location.href;</script>";
    } else {
        echo "<script>alert('Error updating password');</script>";
    }
    $stmt->close();
}

// Fetch users
$sql = "SELECT id, name, email, role FROM users";
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Budget Planner - Manage Users</title>
    <link rel="stylesheet" href="sidebar/style.css">
    <style>
.container {
    max-width: 900px;
    margin: 20px auto;
    padding: 20px;
    background: white;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

h2 {
    text-align: center;
    color: #333;
    margin-top: 10px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    text-align: center;
}

table, th, td {
    border: 1px solid #ddd;
}


th {
    background-color: #007bff;
    color: white;
    padding: 9px 11px;
}

tr:nth-child(even) {
    background-color: #f9f9f9;
}

.btn {
    display: inline-block;
    padding: 8px 12px;
    margin: 10px 8px ;
    font-size: 14px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-align: center;
}

.btn-edit {
    background-color: #ffc107;
    color: white;
}

.btn-password {
    background-color: #17a2b8;
    color: white;
}

.btn-delete {
    background-color: #dc3545;
    color: white;
}

.btn-create {
    background-color: #28a745;
    color: white;
}

.btn:hover {
    opacity: 0.8;
}



label {
    display: block;
    margin: 10px 0 5px;
}

input, select {
    width: 100%;
    padding: 8px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

button[type="submit"] {
    padding: 10px;
    border: none;
    color: white;
    background-color: #007bff;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
}

button[type="submit"]:hover {
    background-color: #0056b3;
}
    </style>
</head>
<body>
<?php include('sidebar/adminnav.html'); ?>
    <div class="container">
    <h2>Create User</h2>
        <form method="POST">
            <label for="newUserName">Name</label>
            <input type="text" name="name" id="newUserName" required>
            <label for="newUserEmail">Email</label>
            <input type="email" name="email" id="newUserEmail" required>
            <label for="newUserPassword">Password</label>
            <input type="password" name="password" id="newUserPassword" required>
            <label for="newUserRole">Role</label>
            <select name="role" id="newUserRole" required>
                <option value="Admin">Admin</option>
                <option value="User">User</option>
            </select>
            <button type="submit" name="create_user" class="btn btn-create">Create User</button>
        </form>
        <h2>User Management</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['role']) ?></td>
                            <td>
                                <button class="btn btn-edit" onclick="editUser(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name']) ?>', '<?= htmlspecialchars($row['email']) ?>', '<?= htmlspecialchars($row['role']) ?>')">Edit</button>
                                <button class="btn btn-password" onclick="changePassword(<?= $row['id'] ?>)">Change Password</button>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit" name="delete_user" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">No users found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <form id="userForm" method="POST" style="display: none;">
            <h2>Edit User</h2>
            <input type="hidden" name="id" id="userId">
            <label for="userName">Name</label>
            <input type="text" name="name" id="userName" required>
            <label for="userEmail">Email</label>
            <input type="email" name="email" id="userEmail" required>
            <label for="userRole">Role</label>
            <select name="role" id="userRole" required>
                <option value="Admin">Admin</option>
                <option value="User">User</option>
            </select>
            <button type="submit" name="update_user" class="btn btn-edit">Save</button>
        </form>

        <form id="passwordForm" method="POST" style="display: none;">
            <h2>Change Password</h2>
            <input type="hidden" name="id" id="passwordUserId">
            <label for="newPassword">New Password</label>
            <input type="password" name="password" id="newPassword" required>
            <button type="submit" name="change_password" class="btn btn-password">Update Password</button>
        </form>

    </div>

    <script>
        function editUser(id, name, email, role) {
            document.getElementById('userId').value = id;
            document.getElementById('userName').value = name;
            document.getElementById('userEmail').value = email;
            document.getElementById('userRole').value = role;
            document.getElementById('userForm').style.display = 'block';
            document.getElementById('passwordForm').style.display = 'none';
        }

        function changePassword(id) {
            document.getElementById('passwordUserId').value = id;
            document.getElementById('passwordForm').style.display = 'block';
            document.getElementById('userForm').style.display = 'none';
        }
    </script>
</body>
</html>
