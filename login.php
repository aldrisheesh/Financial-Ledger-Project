<?php
session_start();
if (isset($_SESSION["user"])){
    header("Location: homepage.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
</head>
<body>
    <div class="container">
        <?php
        if (isset($_POST["login"])) {
            $email = $_POST["email"];
            $password = $_POST["password"];
            require_once "database.php";
            $sql = "SELECT * FROM users WHERE email = '$email'";
            $result = mysqli_query($conn, $sql);
            $user = mysqli_fetch_array($result, MYSQLI_ASSOC);
            if ($user) {
                if(password_verify($password, $user["password"])){
                    session_start();
                    $_SESSION["user"] = "yes";
                    header("Location: homepage.php");
                    die();
                }else{
                    echo "Incorrect password"; // NEEDS STYLING USING CSS
                }
                
            }else{
                echo "Email does not exist"; // NEEDS STYLING USING CSS
            }
        }
        ?>
        <form action="login.php" method="post">
            <div class="form-group">
                <input type="email" placeholder="Enter Email:" name="email">
            </div>
            <div class="form-group">
                <input type="password" placeholder="Enter Password:" name="password">
            </div>
            <div class="form-btn">
                <input type="submit" value="Login" name="login">
            </div>
        </form>
        <div><p>Not registered yet? <a href="registration.php">Register here</a></p></div>
    </div>
</body>
</html>