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
    <title>Registration Form</title>
</head>
<body>
    <div class="container">
        <?php // php code for registration
        if (isset($_POST["submit"])) {
            $fullName = $_POST["fullname"];
            $email = $_POST["email"];
            $password = $_POST["password"];
            $repeatPassword = $_POST["repeat_password"];
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // check for user input errors
            $errors = array();
            if (empty($fullName) || empty($email) || empty($password) || empty($repeatPassword)) {
                array_push($errors, "All fields are required");
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                array_push($errors, "Email is not valid");
            }
            if(strlen($password) < 8){
                array_push($errors, "Password must be at least 8 characters long");
            }
            if($repeatPassword != $password){
                array_push($errors, "Password does not match");
            }

            // repeat email error handling
            require_once "database.php"; // connect to SQL database
            $sql = "SELECT * FROM users WHERE email = '$email'"; // check for repeat emails
            $result = mysqli_query($conn, $sql);
            $rowCount = mysqli_num_rows($result);
            if ($rowCount > 0) {
                array_push($errors, "Email already exists!");
            } 

            if(count($errors) > 0){ // present every error found
                foreach ($errors as $error) {
                    echo "$error <br>"; // NEEDS STYLING USING CSS
                }
            }else{ // insert data to SQL database
                $sql = "INSERT INTO users (full_name, email, password) VALUES ( ?, ?, ? )"; // SQL query that will insert a new row into the 'users' table in the database
                $stmt = mysqli_stmt_init($conn); // initialize prepare statement
                $prepareStmt = mysqli_stmt_prepare($stmt, $sql); // prepares SQL statement for execution
                if ($prepareStmt) {
                    mysqli_stmt_bind_param($stmt, "sss", $fullName, $email, $passwordHash); // binds the actual values to the prepared statement
                    mysqli_stmt_execute($stmt); // inserts the data to database
                    echo "Registered Successfully"; // NEEDS STYLING USING CSS
                } else{
                    die("Something went wrong");
                }
            }
        }
        ?>

        <!-- form needs styling -->
        <form action="registration.php" method="post">
            <div class="form-group">
                <input type="text" name="fullname" placeholder="Full Name:">
            </div>
            <div class="form-group">
                <input type="email" name="email" placeholder="Email:">
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password:">
            </div>
            <div class="form-group">
                <input type="password" name="repeat_password" placeholder="Repeat Password:">
            </div>
            <div class="form-btn">
                <input type="submit" value="Register" name="submit">
            </div>
            <div><p>Already registered? <a href="registration.php">Login here</a></p></div>
        </form>
    </div>
</body>
</html>