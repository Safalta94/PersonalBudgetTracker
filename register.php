<?php
include 'db.php'; // Include database connection
session_start();

// List of allowed email providers
$allowed_domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'live.com'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate input fields
    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $name)) {  // Ensures name contains only letters and spaces
        $error = "Name can only contain letters and spaces.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        // Extract domain from email
        $email_parts = explode('@', $email);
        if (count($email_parts) !== 2 || !in_array(strtolower($email_parts[1]), $allowed_domains)) {
            $error = "Please use a valid email from providers like Gmail, Yahoo, Hotmail, etc.";
        } else {
            // Check if email already exists
            $query = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $query->bind_param("s", $email);
            $query->execute();
            $query->store_result();

            if ($query->num_rows > 0) {
                $error = "Email already exists.";
            } else {
                if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/', $password)) {
                    $error = "Password must be at least 6 characters long, contain at least one uppercase letter, one number, and one special character.";
                } else {
                    // Hash password securely
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Insert user into the database
                    $query = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
                    $query->bind_param("sss", $name, $email, $hashed_password);

                    if ($query->execute()) {
                        $_SESSION['user_id'] = $conn->insert_id;
                        $_SESSION['user_name'] = $name;
                        $_SESSION['user_email'] = $email;
                        $_SESSION['user_role'] = 'user';
                        header("Location: dashboard.php");
                        exit();

                    } else {
                        $error = "Error registering user. Please try again.";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="Styles/register.css">
</head>

<body>

    <div class="register-container">
        <h1>Register</h1>

        <?php if (!empty($error))
            echo "<p style='color:red;'>$error</p>"; ?>

        <form action="register.php" method="POST">
            <label for="name">Name:</label>
            <input type="text" name="name" id="name" required><br>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required><br>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required><br>

            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <br> <a href="login.php">Login into an Account</a></p>
    </div>

</body>

</html>