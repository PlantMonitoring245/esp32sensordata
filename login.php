<?php
include("db_connect.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT id, username, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $username, $hashed_password);
    $stmt->fetch();

    if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
        $_SESSION["user_id"] = $id;
        $_SESSION["username"] = $username;
        header("Location: index.php"); // Redirect to index.php
        exit();
    } else {
        $error_message = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f4f4;
            font-family: 'Poppins', sans-serif;
        }
        .login-container {
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
        .btn-login {
            width: 100%;
            padding: 10px;
            background: #2E7D32;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .btn-login:hover {
            background: #1B5E20;
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
            color: #2E7D32;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Login</h2>
    
    <?php if (isset($error_message)): ?>
        <p class="error-message"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <form id="loginForm" action="" method="POST">
        <input type="email" name="email" id="email" placeholder="Email" required><br>
        <input type="password" name="password" id="password" placeholder="Password" required><br>
        <button type="submit" class="btn-login">Login</button>
    </form>

    <p>Don't have an account? <a href="signup.php">Signup</a></p>
</div>

<script>
document.getElementById("loginForm").addEventListener("submit", function(event) {
    let email = document.getElementById("email").value.trim();
    let password = document.getElementById("password").value.trim();
    let errorMessage = "";

    if (!email.includes("@") || !email.includes(".")) {
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
