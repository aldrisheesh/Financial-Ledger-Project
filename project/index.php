<?php
session_start();
// Redirect to homepage if user is already logged in
if (isset($_SESSION["user"])) {
    header("Location: dashboard.php");
    exit(); // Always exit after redirecting
}
?>
<html>
<head>
    <title>Sign In</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="css/index.css"/>
</head>
<body>
<div class="container">
    <div class="left"></div>
    <div class="middle"></div>
    <div class="right">
        <div class="logo">
            <img alt="App Logo" height="50" src="logo/logo_black.png" width="50"/>
        </div>
        <h2>SIGN IN</h2>
        <?php
        if (isset($_SESSION['message'])) {
            echo "<p class='success-message'>" . $_SESSION['message'] . "</p>";
            unset($_SESSION['message']); // Clear the message after displaying it
        }

        if (isset($_POST["login"])) {
            $email = $_POST["email"];
            $password = $_POST["password"];
            
            require_once "database.php"; // Ensure you have this file and it's correctly set up

            // Use prepared statements to prevent SQL injection
            $sql = "SELECT * FROM user WHERE Email = ?";
            $stmt = mysqli_stmt_init($conn);
            if (mysqli_stmt_prepare($stmt, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $user = mysqli_fetch_array($result, MYSQLI_ASSOC);

                if ($user) {
                    // Verify the password
                    if (password_verify($password, $user["Password"])) {
                        $_SESSION["user"] = [
                            "ID" => $user["UserId"], // Correctly set UserId
                            "Full_Name" => $user["Full_Name"],
                            "Email" => $user["Email"]
                        ]; // Store user information in an associative array
                        header("Location: dashboard.php");
                        exit(); // Always exit after redirecting
                    } else {
                        echo "<p class='error-message'>Incorrect password</p>"; // Styled with CSS
                    }
                } else {
                    echo "<p class='error-message'>Email does not exist</p>"; // Styled with CSS
                }
            } else {
                echo "<p class='error-message'>Database query failed. Please try again later.</p>"; // Handle query failure
            }
            mysqli_stmt_close($stmt); // Close the statement
        }
        ?>
        <form class="login-form" action="index.php" method="post"> <!-- Form action points to the same file -->
            <div class="form-group">
                <input type="email" placeholder="Email Address" name="email" required>
                <i class="fas fa-envelope"></i>
            </div>
            <div class="form-group">
                <input type="password" placeholder="Password" name="password" required>
                <i class="fas fa-lock"></i>
            </div>
            <div class="form-options">
                <label>
                    <input type="checkbox" name="remember"> Remember Me
                </label>
                <a href="#">Forgot Password?</a>
            </div>
            <button class="btn" type="submit" name="login">LOG IN</button>
            <div class="signup">
                Don't have an account? <a href="registration.php">SIGN UP</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>