<?php
include("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);

    // Check if email already exists
    $check_sql = "SELECT id FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $error_message = "Email already registered. Please login.";
    } else {
        // Insert new user
        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $email, $password);

        if ($stmt->execute()) {
            header("Location: index.php"); // Redirect to login page after signup
            exit();
        } else {
            $error_message = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f4f4;
            font-family: 'Poppins', sans-serif;
        }
        .signup-container {
            width: 350px;
            margin: 100px auto;
            padding: 20px;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .btn-signup {
            width: 100%;
            padding: 10px;
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .btn-signup:hover {
            background: #0056b3;
        }
        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }
        p {
            margin-top: 10px;
        }
        a {
            color: #007BFF;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="signup-container">
    <h2>Signup</h2>
    
    <?php if (isset($error_message)): ?>
        <p class="error-message"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <form id="signupForm" action="" method="POST">
        <input type="text" name="username" id="username" placeholder="Username" required><br>
        <input type="email" name="email" id="email" placeholder="Email" required><br>
        <input type="password" name="password" id="password" placeholder="Password" required><br>
        <button type="submit" class="btn-signup">Signup</button>
    </form>

    <p>Already have an account? <a href="login.php">Login</a></p>
</div>

<script>
document.getElementById("signupForm").addEventListener("submit", function(event) {
    let username = document.getElementById("username").value.trim();
    let email = document.getElementById("email").value.trim();
    let password = document.getElementById("password").value.trim();
    let errorMessage = "";

    if (username.length < 3) {
        errorMessage = "Username must be at least 3 characters.";
    } else if (!email.includes("@") || !email.includes(".")) {
        errorMessage = "Please enter a valid email.";
    } else if (password.length < 6) {
        errorMessage = "Password must be at least 6 characters.";
    }

    if (errorMessage) {
        event.preventDefault();
        alert(errorMessage);
    }
});
</script>

</body>
</html>
