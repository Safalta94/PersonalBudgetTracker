<?php
session_start();
include 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Fetch user details
$query = $conn->prepare("SELECT name, email, role FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    if (!empty($name) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $update_query = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $update_query->bind_param("ssi", $name, $email, $user_id);
        if ($update_query->execute()) {
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $success = "Profile updated successfully!";
        } else {
            $error = "Error updating profile.";
        }
    } else {
        $error = "Invalid input data.";
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    // Verify current password
    $pass_query = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $pass_query->bind_param("i", $user_id);
    $pass_query->execute();
    $pass_result = $pass_query->get_result();
    $row = $pass_result->fetch_assoc();

    if (password_verify($current_password, $row['password'])) {
        if (preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/', $new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_pass_query = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_pass_query->bind_param("si", $hashed_password, $user_id);
            if ($update_pass_query->execute()) {
                $success = "Password changed successfully!";
            } else {
                $error = "Error updating password.";
            }
        } else {
            $error = "Password must be at least 6 characters long, contain at least one uppercase letter, one number, and one special character.";
        }
    } else {
        $error = "Current password is incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="Styles/profile.css">
    <link rel="stylesheet" href="Styles/transaction.css">
    <link rel="stylesheet" href="sidebar/style.css">
    <link rel="stylesheet" href="footer/footer.css">
</head>
<body>
    <?php include('sidebar/index.html'); ?>

    <div class="profile-container">
        <h2>User Profile</h2>

        <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <?php if (!empty($success)) echo "<p style='color:green;'>$success</p>"; ?>

        <form method="POST">
            <label>Name:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

            <button type="submit" name="update_profile">Update Profile</button>
        </form>

        <h3>Change Password</h3>
        <form method="POST">
            <label>Current Password:</label>
            <input type="password" name="current_password" required>

            <label>New Password:</label>
            <input type="password" name="new_password" required>

            <button type="submit" name="change_password">Change Password</button>
        </form>

        <a href="dashboard.php">Back to Dashboard</a>
    </div>
    <?php include('footer/footer.html'); ?>

</body>
</html>
