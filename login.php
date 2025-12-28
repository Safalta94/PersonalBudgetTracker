<?php
session_start();
include 'db.php'; // Include database connection

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate input
    if (empty($email) || empty($password)) {
        $error = "Both fields are required.";
    } else {
        // Fetch user from the database
        $query = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $query->bind_param("s", $email);
        $query->execute();
        $query->store_result();

        if ($query->num_rows > 0) {
            $query->bind_result($id, $name, $hashed_password, $role);
            $query->fetch();

            // Verify password
            if (password_verify($password, $hashed_password)) {
                // Start session and store user info
                $_SESSION['user_id'] = $id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = $role;

                // Redirect based on role
                if ($role == 'admin') {
                    header("Location: admin_dashboard.php"); // Redirect to Admin Dashboard
                } else {
                    header("Location: dashboard.php"); // Redirect to User Dashboard
                }
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="Styles/login.css">
</head>

<body>
    <div class="login-container">
        <h1>Login</h1>
        <?php if (!empty($error))
            echo "<p style='color:red;'>$error</p>"; ?>

        <form action="login.php" method="POST">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required><br>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required><br>

            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>

</html>