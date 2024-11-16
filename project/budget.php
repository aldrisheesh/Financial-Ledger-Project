<?php
include_once 'config.php'; // Start the session
// Include the database connection
include_once 'database.php'; 

// Check if the user is logged in
if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_SESSION["user"]["ID"] ?? null; // Access the user ID from the session
    $budgetAmount = $_POST['totalBudget'] ?? 0; // Get the budget amount from the form input

    // Prepare and execute the database insertion
    $currentDate = date("Y-m-d H:i:s");

    // Check if a budget entry already exists for the user
    $checkSql = "SELECT * FROM budget WHERE User_Id = ?";
    $checkStmt = $conn->prepare($checkSql);
    
    if (!$checkStmt) {
        $_SESSION['error'] = "SQL preparation failed: " . $conn->error;
        header("Location: dashboard.php");
        exit();
    }

    $checkStmt->bind_param("i", $userId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        // If a budget entry exists, update it
        $updateSql = "UPDATE budget SET Budget = ?, RDATE = ? WHERE User_Id = ?";
        $updateStmt = $conn->prepare($updateSql);

        if (!$updateStmt) {
            $_SESSION['error'] = "SQL preparation failed: " . $conn->error;
            header("Location: dashboard.php");
            exit();
        }

        // Bind parameters and execute
        $updateStmt->bind_param("ssi", $budgetAmount, $currentDate, $userId);

        if ($updateStmt->execute()) {
            $_SESSION['message'] = 'Budget updated successfully!';
        } else {
            $_SESSION['error'] = 'Error: ' . $updateStmt->error;
        }

        $updateStmt->close();
    } else {
        // If no budget entry exists, insert a new one
        $insertSql = "INSERT INTO budget (User_Id, Budget, RDATE) VALUES (?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);

        if (!$insertStmt) {
            $_SESSION['error'] = "SQL preparation failed: " . $conn->error;
            header("Location: dashboard.php");
            exit();
        }

        // Bind parameters and execute
        $insertStmt->bind_param("ids", $userId, $budgetAmount, $currentDate);

        if ($insertStmt->execute()) {
            $_SESSION['message'] = 'Budget added successfully!';
        } else {
            $_SESSION['error'] = 'Error: ' . $insertStmt->error;
        }

        $insertStmt->close();
    }

    $checkStmt->close();

    // Redirect to dashboard after processing
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget - Expense Manager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="css/budget.css">
    <script>
        function incrementAmount(amount) {
            const totalBudgetInput = document.getElementById('totalBudget');
            let currentAmount = parseInt(totalBudgetInput.value) || 0;
            totalBudgetInput.value = currentAmount + amount;
        }

        function clearAmount() {
            document.getElementById('totalBudget').value = '';
        }
    </script>   
</head>
<body>
    <div class="background-content">
        <?php include 'dashboard.php'; ?>
    </div>
    <div class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add Balance</h2>
                <a class="x-icon" href="dashboard.php">
                    <span class="close">×</span>
                </a> 
            </div>
            <div class="modal-body">
                <form method="post" action="">
                    <h3>Enter Budget</h3>
                    <input id="totalBudget" name="totalBudget" placeholder="0" type="number" required/>
                    <div class="button-grid">
                        <button type="button" onclick="incrementAmount(100)">₱ 100</button>
                        <button type="button" onclick="incrementAmount(500)">₱ 500</button>
                        <button type="button" onclick="incrementAmount(1000)">₱ 1,000</button>
                        <button type="button" onclick="incrementAmount(5000)">₱ 5,000</button>
                        <button type="button" onclick="incrementAmount(10000)">₱ 10,000</button>
                        <button type="button" onclick="incrementAmount(20000)">₱ 20,000</button>
                    </div>
                    <div class="action-buttons">
                        <button type="button" onclick="clearAmount()">Clear</button>
                        <button type="submit">Done</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>